
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
    
@endsection
    
     
@section('body')
<table class="table table-bordered table-sm">
<colgroup class="basic_information" span="1" width="170px"></colgroup>
<tbody>
	@if ($small)
	<tr>
		<td>予約空き枠</td>
		<td style="text-align:right">{{$reservation_available}}件</td>		
	</tr>
	<tr>
		<td>当日空き枠</td>
		<td style="text-align:right">{{$reservation_available_today}}件</td>		
	</tr>	
	@else
	<tr>
		<td><div class="h3">予約空き枠</div></td>
		<td style="text-align:right"><div class="h3">{{$reservation_available}}件</div></td>		
	</tr>
	<tr>
		<td><div class="h3">当日空き枠</div></td>
		<td style="text-align:right"><div class="h3">{{$reservation_available_today}}件</div></td>		
	</tr>
	<tr>
		<td><div class="h3">予約可能日</div></td>
		<td style="text-align:right">
			<div class="h3">
				@if ($reservation_available <= 0)
					@if ($reservation_available_today > 0)
						当日受付中
					@else
						受付停止中
					@endif
				@else
					{{$first_reservation_date.'～'}}
				@endif	
			</div>
		</td>	
	</tr>
	@endif	
				
</tbody>
</table>   
@endsection   　 