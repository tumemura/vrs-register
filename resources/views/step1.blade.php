@extends('base')

@section('title','お名前入力')

@section('css')
<link href="{{ asset('/css/style.css') }}" rel='stylesheet'>
@component('bootstrap_css')
@endcomponent
@endsection

@section('script')
@component('bootstrap_script')
@endcomponent

<style>

.long-btn {
	width: 100%;
}

</style>

@endsection

@section('body')

<div class="container main-container">
<div class="row justify-content-center mb-5">
<span class="h4">該当データが見つかりません</span>
</div>
<div class="row justify-content-center mb-5">
<span class="h3">以下の選択肢の中からお選びください</span>
</div>
<div class="row justify-content-center mt-5">
	<div class="d-flex flex-column">
		<div class="mt-5">
			<button class="btn btn-success long-btn" type="button" onclick="document.search_form.submit();">コロナワクチンを全て当院で接種されたかた</button>
		</div>
		<div class="mt-5">
			<button class="btn btn-success long-btn" type="button" onclick="document.new_form.submit();">他院(集団接種も含む)で接種されたかた</button>
		</div>
		<div class="mt-5">
			<button class="btn btn-success long-btn" type="button" onclick="location.href='/';">接種券番号や生年月日を再入力する場合</button>
		</div>
			<div class="mt-5">
		</div>
	</div>
</div>

<!--  情報検索のケース　-->
<form name="search_form" action="/step1s" method="post" autocomplete="off" novalidate>
@csrf
	<input type="hidden" name="municipal_code" value="{{$municipal_code}}">
	<input type="hidden" name="coupon_code" value="{{$coupon_code}}">
	<input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">
	<input type="hidden" name="category_id" value="1">
	<input type="hidden" name="office" value="">
	<input type="hidden" name="first_name" value="">
	<input type="hidden" name="last_name" value="">
	<input type="hidden" name="from" value="/step1">	
</form>

<!--  新規申し込みのケース　-->
<form name="new_form" action="/step2" method="post" autocomplete="off" novalidate>
	@csrf
	<input type="hidden" name="municipal_code" value="{{$municipal_code}}">
	<input type="hidden" name="coupon_code" value="{{$coupon_code}}">
	<input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">
	<input type="hidden" name="category_id" value="1">
	<input type="hidden" name="office" value="">
	<input type="hidden" name="first_name" value="">
	<input type="hidden" name="last_name" value="">
	<input type="hidden" name="from" value="/step1">	
</form>

<!--  名前でデータ検索　-->



</div>
@endsection

