@extends('base')

@section('title','該当記録発見')

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
		<span class="h3">該当記録が見つかりました</span>
		</div>
		<div class="row justify-content-center mb-5">
		<span class="h4">今後は接種券番号と生年月日でログインできます</span>
		</div>
		<div class="row justify-content-center mt-5">
			<div class="form-group col-6">
				<button class="btn btn-success fit-btn" id="next-btn" type="button" onclick="location.href = '/mypage';">接種予約にすすむ</button>
			</div>
		</div>
</div>
@endsection

