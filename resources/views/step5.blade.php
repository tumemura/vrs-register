@extends('base')

@section('title','連絡先入力')

@section('css')
<link href="{{ asset('/css/style.css') }}" rel='stylesheet'>
@component('bootstrap_css')
@endcomponent
@endsection

@section('script')
@component('bootstrap_script')
@endcomponent

<script>
(function() {
    'use strict';
    window.addEventListener('load',function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit',function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            },false);
        });
    },false);
})();

function hankaku(str) {
	return str.replace(/[Ａ-Ｚａ-ｚ０-９]/g, function(s) {
    	return String.fromCharCode(s.charCodeAt(0) - 65248);
	});
}

$(function() {
	$('#phone').on('change',function(){
		let cc = $('#phone').val();
		// ハイフンの削除
		cc = cc.replace(/－/g,'');
		cc = cc.replace(/-/g,'');

		$('#phone').val(hankaku(cc));
	});
});



</script>

@endsection



@section('body')


<div class="container main-container">
<form class="needs-validation" action="/step6" method="post" autocomplete="off" novalidate>
@csrf
<div class="row justify-content-center mb-5">
	<span class="h3">連絡先入力</span>
</div>
<div class="form-row justify-content-center">
	<div class="form-group col-12">
		<label for="phone">電話番号&nbsp;<span class="badge badge-danger">必須</span></label>
		<input class="form-control" type="tel" name="phone" id="phone" value="" inputmode="numeric" pattern="[0-9]{10,11}" placeholder="09012345678" required>
		<div class="invalid-feedback">
			電話番号をハイフン無しで入力してください
		</div>
	</div>				
</div>
<div class="form-row justify-content-center">
	<div class="form-group col-12">
		<label for="email">メールアドレス&nbsp;<span class="badge badge-secondary">任意</span></label>
		<input class="form-control" type="email" name="email" id="email" value="" placeholder="yamada.taro@email.com">
		<div class="invalid-feedback">
			メールアドレスを入力してください
		</div>
	</div>
</div>
<div class="row justify-content-center mt-5">
	<div class="form-group col-6">
		<button class="btn btn-outline-primary fit-btn" id="back-btn" type="button" onclick="document.back_form.submit();">戻る</button>
	</div>
	<div class="form-group col-6">
		<button class="btn btn-success fit-btn" id="next-btn" type="submit">次へ</button>
	</div>
</div>
<input type="hidden" name="municipal_code" value="{{$municipal_code}}">
<input type="hidden" name="coupon_code" value="{{$coupon_code}}">
<input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">	
<input type="hidden" name="first_name" value="{{$first_name}}">
<input type="hidden" name="last_name" value="{{$last_name}}">	
<input type="hidden" name="category_id" value="{{$category_id}}">
<input type="hidden" name="office" value="{{$office}}">
<input type="hidden" name="second_dose_date" value="{{$second_dose_date}}">
<input type="hidden" name="third_dose_date" value="{{$third_dose_date}}">
<input type="hidden" name="fourth_dose_date" value="{{$fourth_dose_date}}">
<input type="hidden" name="fifth_dose_date" value="{{$fifth_dose_date}}">
</form>
</div>


<form action="/step4" name="back_form" method="post" autocomplete="off" >
@csrf
<input type="hidden" name="municipal_code" value="{{$municipal_code}}">
<input type="hidden" name="coupon_code" value="{{$coupon_code}}">
<input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">	
<input type="hidden" name="first_name" value="{{$first_name}}">
<input type="hidden" name="last_name" value="{{$last_name}}">	
<input type="hidden" name="category_id" value="{{$category_id}}">
<input type="hidden" name="office" value="{{$office}}">
<input type="hidden" name="second_dose_date" value="{{$second_dose_date}}">
<input type="hidden" name="third_dose_date" value="{{$third_dose_date}}">
<input type="hidden" name="fourth_dose_date" value="{{$fourth_dose_date}}">
<input type="hidden" name="fifth_dose_date" value="{{$fifth_dose_date}}">
</form>

@endsection

