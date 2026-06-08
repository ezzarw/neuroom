<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    protected function transformNote(Note $note): array
    {
        return [
            'id' => $note->id,
            'user_id' => $note->user_id,
            'title' => $note->title,
            'content' => $note->content,
            'created_at' => $this->formatDateTime($note->created_at),
            'updated_at' => $this->formatDateTime($note->updated_at),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('s', ''));
        $showAll = $request->boolean('all');
        $perPage = 10;

        if ($search !== '') {
            $query = Note::search($search)
                ->query(fn (Builder $query) => $query
                    ->where('user_id', Auth::id())
                    ->latest());
        } else {
            $query = Note::query()
                ->where('user_id', Auth::id())
                ->latest();
        }

        if ($showAll) {
            $notes = $query->get();

            return $this->apiSuccess(
                'Berhasil menampilkan semua catatan.',
                $notes
                    ->map(fn (Note $note) => $this->transformNote($note))
                    ->values()
                    ->all()
            );
        }

        $notes = $query->paginate($perPage);

        return $this->apiSuccess('Berhasil menampilkan catatan.', [
            'items' => $notes
                ->getCollection()
                ->map(fn (Note $note) => $this->transformNote($note))
                ->values()
                ->all(),
            'pagination' => [
                'current_page' => $notes->currentPage(),
                'per_page' => $notes->perPage(),
                'total' => $notes->total(),
                'last_page' => $notes->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request)
    {
        $note = Note::query()->create([
            'user_id' => Auth::id(),
            ...$request->validated(),
        ]);

        ActivityLogger::log(Auth::id(), 'create_note', "User membuat catatan: {$note->title}");

        return $this->apiSuccess(
            'Catatan berhasil dibuat.',
            $this->transformNote($note),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $note = Note::query()
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if ($note === null) {
            return $this->apiError('Catatan tidak ditemukan.', 404);
        }

        return $this->apiSuccess(
            'Berhasil menampilkan catatan.',
            $this->transformNote($note)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoteRequest $request, string $id)
    {
        $note = Note::query()
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if ($note === null) {
            return $this->apiError('Catatan tidak ditemukan.', 404);
        }

        $note->update($request->validated());
        $note->refresh();

        ActivityLogger::log(Auth::id(), 'update_note', "User mengubah catatan: {$note->title}");

        return $this->apiSuccess(
            'Catatan berhasil diupdate.',
            $this->transformNote($note)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $note = Note::query()
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if ($note === null) {
            return $this->apiError('Catatan tidak ditemukan.', 404);
        }

        $noteTitle = $note->title;
        $note->delete();

        ActivityLogger::log(Auth::id(), 'delete_note', "User menghapus catatan: {$noteTitle}");

        return $this->apiSuccess('Catatan berhasil dihapus.');
    }
}
