
@extends('base')

@section('title','予約状況')

@section('css')
<link href="{{ asset('/css/style.css') }}" rel='stylesheet'>
@component('bootstrap_css')
@endcomponent

<style>

body {
    line-height: 0rem !important;
}

.h3 {
    margin-bottom: 0px !important;
    font-family: "トーキング" !important;
}

</style>
@endsection

@section('script')
@component('bootstrap_script')
@endcomponent

<script type="text/javascript" src="//webfonts.xserver.jp/js/xserver.js"></script>
    
<script type="text/javascript">
	window.onload = function() {
		var height = document.getElementsByTagName("html")[0].scrollHeight;
		window.parent.postMessage(["setHeight", height], "*");
	}
</script>

@endsection
    
     
@section('body')

@foreach($vaccine_list as $vaccine_id => $value)

<h2 style="text-align:center">{{ $value['name'] }}</h2>
<table class="table table-bordered table-sm">
<colgroup class="basic_information" span="1" width="170px"></colgroup>
<tbody>
	@if ($small)
	<tr>
		<td>予約空き枠</td>
		<td style="text-align:right">{{ $value['available'] }}件</td>		
	</tr>	
	@else
	<tr>
		<td><div class="h3">予約空き枠</div></td>
		<td style="text-align:right"><div class="h3">{{ $value['available'] }}件</div></td>		
	</tr>
	<tr>
		<td><div class="h3">予約可能日</div></td>
		<td style="text-align:right">
			<div class="h3">
				@if ($value['available'] <= 0)
						受付停止中
				@else
					{{ $value['first_reservation_date'] }}～
				@endif	
			</div>
		</td>	
	</tr>
	@endif	
				
</tbody>
</table>
@endforeach   
@endsection   　 