@extends('base')

@section('title','登録内容確認')

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


</script>

@endsection



@section('body')


<div class="container main-container">
<form class="needs-validation" action="/register" method="post" autocomplete="off" novalidate>
@csrf
<div class="row justify-content-center mb-5">
<span class="h3">登録内容確認</span>
</div>

<table class="table table-bordered">
<colgroup span="1" width="150px" style="background-color:#f3f3f3;"></colgroup>
<tbody>
	<tr><td>市町村コード</td><td>{{$municipal_code}}</td></tr>
	<tr><td>接種券番号</td><td>{{$coupon_code}}</td></tr>
	<tr><td>名前</td><td>{{$last_name}} {{$first_name}}</td></tr>
	<tr><td>生年月日</td><td>{{$date_of_birth}}</td></tr>
	<tr><td>電話番号</td><td>{{ strlen($phone)==11?substr($phone,0,3).'-'.substr($phone,3,4).'-'.substr($phone,7,4):substr($phone,0,2).'-'.substr($phone,2,4).'-'.substr($phone,6,4)}}</td></tr>
	<tr><td>メールアドレス</td><td>{{$email}}</td></tr>
    @if ($second_dose_date)
        <tr><td>２回目接種日</td><td>{{$second_dose_date}}</td></tr>
    @elseif ($third_dose_date)
        <tr><td>３回目接種日</td><td>{{$third_dose_date}}</td></tr>
    @elseif ($fourth_dose_date)
        <tr><td>４回目接種日</td><td>{{$fourth_dose_date}}</td></tr>
    @endif
</tbody>
</table>

<div class="row justify-content-center mt-5">
<div class="form-group col-6">
<button class="btn btn-outline-primary fit-btn" id="back-btn" type="button" onclick="document.back_form.submit();">戻る</button>
</div>
<div class="form-group col-6">
<button class="btn btn-success fit-btn" id="next-btn" type="submit">登録する</button>
</div>
</div>
<input type="hidden" name="municipal_code" value="{{$municipal_code}}">
<input type="hidden" name="coupon_code" value="{{$coupon_code}}">
<input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">
<input type="hidden" name="first_name" value="{{$first_name}}">
<input type="hidden" name="last_name" value="{{$last_name}}">
<input type="hidden" name="phone" value="{{$phone}}">
<input type="hidden" name="email" value="{{$email}}">
<input type="hidden" name="office" value="{{$office}}">
<input type="hidden" name="second_dose_date" value="{{$second_dose_date}}">
<input type="hidden" name="third_dose_date" value="{{$third_dose_date}}">
<input type="hidden" name="fourth_dose_date" value="{{$fourth_dose_date}}">
<input type="hidden" name="category_id" value="{{$category_id}}">
</form>
</div>


<form action="/step5" name="back_form" method="post" autocomplete="off" >
@csrf
<input type="hidden" name="municipal_code" value="{{$municipal_code}}">
<input type="hidden" name="coupon_code" value="{{$coupon_code}}">
<input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">
<input type="hidden" name="first_name" value="{{$first_name}}">
<input type="hidden" name="last_name" value="{{$last_name}}">
<input type="hidden" name="phone" value="{{$phone}}">
<input type="hidden" name="email" value="{{$email}}">
<input type="hidden" name="office" value="{{$office}}">
<input type="hidden" name="second_dose_date" value="{{$second_dose_date}}">
<input type="hidden" name="third_dose_date" value="{{$third_dose_date}}">
<input type="hidden" name="fourth_dose_date" value="{{$fourth_dose_date}}">
<input type="hidden" name="category_id" value="{{$category_id}}">
</form>
@endsection

