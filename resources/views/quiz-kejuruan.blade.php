@extends('layouts.app')

@section('title','Quiz Kejuruan')

@section('content')
<section class="section">
<div class="container">

<div class="back-wrapper">
    <a href="{{ route('belajar') }}" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<h1 class="headline">Pilih Soal Quiz Kejuruan</h1>

<div class="search-box">
    <input type="text" id="searchInput" placeholder="Cari soal...">
</div>

<div class="quiz-grid" id="quizList"></div>

</div>
</section>
@endsection

@section('scripts')
<script>
const kategori = "kejuruan";
</script>
<script src="{{ asset('js/quiz-list.js') }}"></script>
@endsection