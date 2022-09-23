@extends('base')

@section('title','登録完了')

@section('css')
<link href="{{ asset('/css/style.css') }}" rel='stylesheet'>
@component('bootstrap_css')
@endcomponent
@endsection

@section('script')
@component('bootstrap_script')
@endcomponent


@endsection



@section('body')


<div class="container main-container">
<div class="row justify-content-center mb-5">
<span class="h3">登録手続きが完了しました</span>
</div>
<div class="row justify-content-center mb-5">
<span>続けてマイページでご予約手続きをしてください</span>
</div>
<div class="row justify-content-center mt-5">
<div class="form-group col-5">
<button class="btn btn-outline-primary fit-btn" id="back-btn" type="button" onclick="location.href='/mypage'">マイページ</button>
</div>
</div>
</div>
@endsection

