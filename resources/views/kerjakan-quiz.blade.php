@extends('layouts.app')

@section('title','Kerjakan Quiz')

@section('content')
<section class="section">
<div class="container">

<div class="quiz-card">
    <h2 id="quiz-title"></h2>
    <div id="timer"></div>

    <div class="progress-bar">
        <div class="progress-fill" id="progressFill"></div>
    </div>

    <div id="question"></div>
    <div id="options"></div>

    <button class="btn-primary full" id="nextBtn">Next</button>
</div>

</div>
</section>
@endsection

@section('scripts')
<script src="{{ asset('js/quiz-play.js') }}"></script>
@endsection