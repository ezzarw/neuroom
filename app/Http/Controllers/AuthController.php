<?php

namespace App\Http\Controllers;

use App\Models\Authentication;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // validate
        $request->validate([
            'username' => ['string', 'required', 'max:100'],
            'password' => ['string', 'required', 'min:8'],
            'email' => ['string', 'required', 'email', 'max:100', 'unique:authentications,email'],
        ]);
        
        $display_name = $request->username;
        $natural_username = $request->username;
        $natural_password = $request->password;
        $email = $request->email;
        // validate end

        
        // make username unique
        $base = Str::slug($natural_username, '');
        $unique_username = $base;
        
        $exists = User::where('username', $unique_username)->exists();
        
        if ($exists) {
            $taken = User::where('username', 'like', $base.'%')
            ->pluck('username')
            ->all();
            
            // cari suffix angka terbesar
            $max = 1;
            foreach ($taken as $u) {
                if ($u === $base) {
                    $max = max($max, 1);
                    continue;
                }
                if (preg_match('/^'.preg_quote($base, '/').'(\d+)$/', $u, $m)) {
                    $max = max($max, (int)$m[1]);
                }
            }
            
            $unique_username = $base . ($max + 1);
        }
        // make username unique end
        
        

        
        // password hashing
        if (PHP_OS_FAMILY == "Windows") {
            $bin = base_path('go\bin\win\hashingbcry.exe');
        } else if (PHP_OS_FAMILY == "Linux") {
            $bin = base_path('go/bin/hashingbcry');
        }
        
        $process = $this->goProcess([$bin, '-e', $natural_password]);
        $process->setTimeout(4);
        $process->run();
        if ($process->isSuccessful() == false) {
            logger()->error('ada kesalahan pada hashingbcry', ['err' => $process->getErrorOutput()]);

            return back()
                ->withInput()
                ->with('auth_popup', 'register-popup')
                ->withErrors(['register' => 'Terjadi kesalahan internal.']);
        }
        $hashed_password = trim($process->getOutput());
        // password hashing end

        
        // saveToDB
        // Simpan auth + profile dalam satu transaksi agar tidak setengah jadi.
        $user_data = DB::transaction(function () use ($unique_username, $email, $hashed_password, $display_name) {
            $auth = Authentication::create([
                'username' => $unique_username,
                'email' => $email,
                'is_admin' => 0,
                'password' => $hashed_password,
            ]);

            $users = User::create([
                'username' => $unique_username,
                'display_name' => $display_name,
                'email' => $email,
                'profile_picture' => null,
            ]);
            $result = (object) ['auth' => $auth, 'user' => $users];
            return $result;
        });
        // saveToDB end

        Auth::guard('web')->login($user_data->auth);
        $request->session()->regenerate();

        return redirect()
            ->route('utama')
            ->with('success', 'Register berhasil. Selamat datang.');
    }




    // login

    public function login(Request $request)
    {
        // validasi input
        $request->validate([
            'email' => 'required|string|email|max:100',
            'password' => 'required|string',
        ]);
        $email = $request->email;
        $password = $request->password;
        // validasi input end   

        // Ngambil data dari database
        $auth = Authentication::query()->where('email', $email)->first();
        $user = User::query()->where('email', $email)->first();

        if ($auth == null || $user == null) {
            return back()
                ->withInput($request->only('email'))
                ->with('auth_popup', 'login-popup')
                ->withErrors(['login' => 'Email atau password salah.']);
        }
        // Ngambil data dari database end

        // validasi password
        $password_from_db = $auth->password;
        if ($password_from_db == null) {
            return back()
                ->withInput($request->only('email'))
                ->with('auth_popup', 'login-popup')
                ->withErrors(['login' => 'Email atau password salah.']);
        }
            // verifikasi password
        $isPasswordValid = password_verify($password, $password_from_db);
        if (! $isPasswordValid) {
            if (PHP_OS_FAMILY == "Windows") {
                $bin  = base_path('go\bin\win\hashingbcry.exe');
            } elseif (PHP_OS_FAMILY == "Linux") {
                $bin  = base_path('go/bin/hashingbcry');
            }
            $process = $this->goProcess([$bin, '-v', $password, $password_from_db]);
            $process->setTimeout(4);
            $process->run();

            if ($process->isSuccessful()) {
                $validated = trim($process->getOutput());
                $isPasswordValid = $validated === 'Bcrypt matched';
            } else {
                logger()->warning('fallback hashingbcry gagal saat login', ['err' => $process->getErrorOutput()]);
            }   
        }

        if (! $isPasswordValid) {
            return back()
                ->withInput($request->only('email'))
                ->with('auth_popup', 'login-popup')
                ->withErrors(['login' => 'Email atau password salah.']);
        }
            // verifikasi password end
        // validasi password end
        
        // regenerate session   
        Auth::guard('web')->login($auth);
        $request->session()->regenerate();

        return redirect()
            ->route((int) $auth->is_admin === 1 ? 'admin.dashboard' : 'utama')
            ->with('success', 'Login berhasil.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();  
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', 'Logout berhasil.');
    }



    

    public function me(Request $request)
    {
        $auth = $request->user();

        $user = User::query()
            ->where('username', $auth->username)
            ->first();

        return view('auth.me', [
            'auth' => $auth,
            'user' => $user,
        ]);
    }
}
