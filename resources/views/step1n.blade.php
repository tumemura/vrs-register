@extends('base')

@section('title','該当記録無し')

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
		<span class="h3">該当記録は見つかりませんでした</span>
		</div>
		<div class="row justify-content-center mb-5">
		<p>
		<span class="h4">お手数ですが当院ワクチン専用ダイヤル{{env('CONTACT_PHONE','???-????-????')}}までご連絡ください</span>
		</div>
		<div class="row justify-content-center mt-5">
			<div class="form-group col-6">
				<button class="btn btn-success fit-btn" id="next-btn" type="button" onclick="location.href='/';">トップページへ戻る</button>
			</div>
		</div>
	</form>
</div>
@endsection

