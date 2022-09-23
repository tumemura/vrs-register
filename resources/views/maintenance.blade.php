@extends('base')

@section('css')

@component('bootstrap_css')
@endcomponent

<link href="{{ asset('/css/style.css') }}" rel='stylesheet'>

@endsection

@section('script')

@component('bootstrap_script')
@endcomponent

@endsection

@section('body')



<div class="container main-container">
<div class="row justify-content-center my-5">
<H2>メンテナンスモード</H2>
</div>
<div class="row justify-content-center my-5">
	<p>現在サイトはメンテナンス中です。<p>
	<p>しばらくたってから再度お試しください。</p>
</div>
<div class="row justify-content-center my-5">
<button class="btn btn-primary fit-btn" id="register-btn" type="button" onclick="location.replace('{{env('TOP_URL','/')}}');">ホーム</button>
</div>
</div>
@endsection

