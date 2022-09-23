
@extends('base')

@section('title','医療・介護・保育従事者専用窓口')

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
				// 年齢チェック
				let dob = $('#date_of_birth').val();
				if (dob.length == 8) {
					let year = dob.substr(0,4);
					let month = parseInt(dob.substr(4,2));
					let day = parseInt(dob.substr(6,2));
					let today = new Date();

					let age = today.getFullYear() - year;
					let monthDay1 = month*100 + day;
					let monthDay2 = (today.getMonth()+1)*100+today.getDate();
					if (monthDay1 > monthDay2)
						age--;

					if (age < {{env('MINIMUM_AGE')}}) {
						event.preventDefault();
						event.stopPropagation();
						$('#errorDialogMessage').text('現在は{{env('MINIMUM_AGE')}}歳以上の方のみご登録・ご予約が可能です');	
						$('#errorDialog').modal();
					}
					if (dob > {{env('MAXIMUM_DOB','99999999')}}) {
						event.preventDefault();
						event.stopPropagation();
						let maxdob = '{{env('MAXIMUM_DOB')}}';
						let maxyear = maxdob.substr(0,4);
						let maxmonth = parseInt(maxdob.substr(4,2));
						let maxday = parseInt(maxdob.substr(6,2));
						$('#errorDialogMessage').text('誕生日が'+maxyear + '/' + maxmonth + '/' + maxday + 'まで方のみご登録・ご予約が可能です');	
						$('#errorDialog').modal();
					}
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
let sapporoCode = '011002';
$(function() {

	$('#city').change(function() {
		$('#municipal_code').val( $('option:selected',this).val());
		if ($('#municipal_code').val() == sapporoCode) {
			$('#municipal_block').hide();
		} else {
			$('#municipal_block').show();
		}		
	})

	$('#coupon_code').on('change',function(){
		let cc = $('#coupon_code').val();
		// ハイフンの削除
		cc = cc.replace(/－/g,'');
		cc = cc.replace(/-/g,'');

		$('#coupon_code').val(hankaku(cc));
	});
	
	
	$('#date_of_birth').on('change',function(){
		$('#date_of_birth').val(hankaku($('#date_of_birth').val()));
	});
	
	if ($('#municipal_code').val() == '') {
		$('#municipal_code').val(sapporoCode);
	}
	
	if ($('#municipal_code').val() == sapporoCode) {
		$('#municipal_block').hide();
	} else {
		$('#municipal_block').show();
	}

	@if (!empty($error))
		$('#errorDialog').modal();
	@endif

});

</script>


</script>	
	
@endsection



@section('body')

<!-- Modal -->
<div class="modal" id="errorDialog" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="errorDialogTitle">登録・ログインエラー</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="errorDialogMessage">
		  {{$error}}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="close-btn" data-dismiss="modal">閉じる</button>
      </div>
    </div>
  </div>
</div>

<div class="container main-container">
<form class="needs-validation" action="/splogin" method="post" autocomplete="off" novalidate> 
		@csrf
		<div class="row justify-content-center mb-5">
			<h3>医療・介護・保育従事者</h3><br><br>
			<h3>専用ワクチン予約窓口</h3>
			<p>
			<span class="h5 text-danger">注意：対象外の方によるご予約は全て取り消させていただきます</span>
		</div>
		<div class="form-row my-3">
	    	<div class="form-group col-12">
				<label for="city">お住まい&nbsp;<span class="badge badge-danger">必須</span></label> 
				<select class="form-control" id="city" name="city" required>
						<option value="011002" @if(old('city')==="011002") selected @endif>札幌市</option>
						<option value="" @if(old('city')==="") selected @endif>札幌市以外</option>
				</select>
				<span class="small">札幌市以外にお住まいの場合は変更してください</span>
				<div class="invalid-feedback">お住まいの市町村を選択してください</div>
			</div>		
		</div>
		<div id="municipal_block">
		<div class="form-row my-3">
			<div class="form-group col-12">
	    		<a href="https://www.city.sapporo.jp/2019n-cov/vaccine/jyuuminnhyo.html" target="_blank"><span>(重要）住民票所在地以外でのワクチン接種について</span></a>
			</div>
		</div>
		<div class="form-row my-3">
			<div class="form-group col-12">
	    		<label for="municipal_code">市町村コード&nbsp;<span class="badge badge-danger">必須</span></label>
				<input class="form-control" type="text" name="municipal_code" id="municipal_code" value="{{old('municipal_code')}}" inputmode="numeric" pattern="\d{6}" placeholder="000000" required>
				<div class="invalid-feedback">市町村コードを半角数字6桁で入力してください</div>
			</div>
		</div>
		</div>
		<div class="form-row my-3">
			<div class="form-group col-12">
	    		<label for="coupon_code">接種券番号&nbsp;<span class="badge badge-danger">必須</span></label>
				<input class="form-control" type="text" name="coupon_code" id="coupon_code" value="{{old('coupon_code')}}" inputmode="numeric" pattern="\d{10}" placeholder="0000000000" required>
				<div class="invalid-feedback">接種券番号を半角数字10桁で入力してください</div>
			</div>
		</div>
		<div class="form-row">
		<div class="form-group col-12">
			<label for="date_of_birth">生年月日&nbsp;<span class="badge badge-danger">必須</span></label>
			<input class="form-control" type="text" name="date_of_birth" id="date_of_birth" value="" inputmode="numeric" pattern="\d{8}" placeholder="19210501" required>
			<span class="small">例： 1921年5月1日 -> 19210501</span>
			<div class="invalid-feedback">生年月日を半角西暦８桁で入力してください</div>
		</div>
		</div>
		<div class="row justify-content-center mt-5">
		<div class="form-group col-12">
			<button class="btn btn-success fit-btn" id="next-btn" type="submit">次へ</button>
		</div>
		</div>
</form>
</div>
@endsection

