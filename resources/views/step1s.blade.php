@extends('base')

@section('title','接種記録検索')

@section('css')
<link href="{{ asset('/css/style.css') }}" rel='stylesheet'>
@component('bootstrap_css')
@endcomponent
@endsection

@section('script')
@component('bootstrap_script')
@endcomponent

<script>

$(function() {
	@if (Session::has('error'))
		$('#errorDialog').modal();
	@endif

	document.getElementById('search_method').value = '{{old('search_method','name')}}';
	search_method_changed();
});

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


function search_method_changed() {
	if (document.getElementById("search_method").value == "name") {
		document.getElementById("name_row").style.display ="block";
		document.getElementById("phone_row").style.display ="none";
		document.getElementById("first_name").required = true;
		document.getElementById("last_name").required = true;
		document.getElementById("phone").required = false;
	} else {
		document.getElementById("phone_row").style.display ="block";
		document.getElementById("name_row").style.display ="none";
		document.getElementById("first_name").required = false;
		document.getElementById("last_name").required = false;
		document.getElementById("phone").required = true;
	}
}


</script>



@endsection

@section('body')

<!-- Modal -->
<div class="modal" id="errorDialog" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="errorDialogTitle">エラー</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="errorDialogMessage">
	  	{!! Session::get('error','') !!}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="close-btn" data-dismiss="modal">閉じる</button>
      </div>
    </div>
  </div>
</div>

<div class="container main-container">
	<form class="needs-validation" action="/step1r" method="post" autocomplete="off" novalidate>
		@csrf
		<div class="row justify-content-center mb-5">
		<span class="h3">接種記録検索</span>
		</div>
		<div class="row justify-content-center mb-5">
		<span class="h4">接種記録を検索しますのでお名前または電話番号を入力してください</span>
		</div>
		<div class="form-row">
			<div class="form-group col-6">
				<label>検索方法</label>
			</div>
			<div class="form-group col-6">
			<select id="search_method" name="search_method" class="form-control" onchange="search_method_changed();" >
	   	 			    <option value="name">お名前</option>
					    <option value="phone">電話番号</option>
				</select>
			</div>
		</div>
		<div class="form-row" id="name_row">
			<div class="form-group col-12">
				<label for="last_name">お名前(姓)&nbsp;<span class="badge badge-danger">必須</span></label>
				<input class="form-control" type="text" name="last_name" id="last_name" value="{{old('last_name')}}" placeholder="豊生" required>
			</div>
			<div class="form-group col-12">
				<label for="first_name">お名前(名)&nbsp;<span class="badge badge-danger">必須</span></label>
				<input class="form-control" type="text" name="first_name" id="first_name" value="{{old('first_name')}}" placeholder="太郎" required>
			</div>
		</div>
		<div class="form-row" id="phone_row" style="display:none">
			<div class="form-group col-12">
				<label for="phone">電話番号&nbsp;<span class="badge badge-danger">必須</span></label>
				<input class="form-control" type="tel" name="phone" id="phone" value="{{old('phone')}}"  inputmode="numeric" pattern="[0-9]{10,11}" placeholder="09012345678">
				<div class="invalid-feedback">
					電話番号をハイフン無しで入力してください
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
		<input type="hidden" name="category_id" value="{{$category_id}}">
		<input type="hidden" name="municipal_code" value="{{$municipal_code}}">
		<input type="hidden" name="coupon_code" value="{{$coupon_code}}">
		<input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">	
		<input type="hidden" name="office" value="">	
	</form>
</div>


<form action="/step1" name="back_form" method="post" autocomplete="off" >
@csrf
    <input type="hidden" name="municipal_code" value="{{$municipal_code}}">
    <input type="hidden" name="coupon_code" value="{{$coupon_code}}">
    <input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">	
</form>

@endsection

