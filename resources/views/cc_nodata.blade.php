@extends('base')

@section('title','該当データ無し')

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
	<form action="/step2" method="post" autocomplete="off">
		@csrf
		<div class="row justify-content-center mb-5">
		<span class="h3">該当データは見つかりませんでした</span>
		</div>
		<div class="row justify-content-center mb-5">
		<p>
		<span class="h4">接種券記載の３回目ワクチン接種日をご確認の上、</span>
		<span class="h4">このまま新規データ登録にお進みください</span>
		</div>
		<div class="row justify-content-center mt-5">
			<div class="form-group col-6">
				<button class="btn btn-success fit-btn" id="next-btn" type="button" onclick="submit();">新規登録にすすむ</button>
			</div>
		</div>
		<input type="hidden" name="office" value="{{$office}}">
		<input type="hidden" name="municipal_code" value="{{$municipal_code}}">
		<input type="hidden" name="coupon_code" value="{{$coupon_code}}">
		<input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">	
		<input type="hidden" name="category_id" value="3">	
		<input type="hidden" name="first_name" value="{{$first_name}}">	
		<input type="hidden" name="last_name" value="{{$last_name}}">	
		<input type="hidden" name="from" value="/cc_nodata">	
	</form>
</div>
@endsection

