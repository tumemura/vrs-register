@extends('base')

@section('css')

@component('bootstrap_css')
@endcomponent

<link href="{{ asset('/css/style.css') }}" rel='stylesheet'>

@endsection

@section('script')

@component('bootstrap_script')
@endcomponent

<script>

$(function(){
    $(".reserve-btn").on('click',function() {
        var frame_id = $(this).data('frame_id');
        location.href='/reserve/'+frame_id;
    });    
	
	@if (Session::has('error'))
		$('#errorDialog').modal();
	@endif
});


</script>
    
@endsection
    
@section('body')

<style>

.main {
    height: 90%;
    overflow-y:auto;
    overflow-x:hidden;
}

</style>


<!-- Modal -->
<div class="modal" id="errorDialog" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="errorTitle">エラー</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
	 	 {!! Session::get('error','') !!}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="close-btn" data-dismiss="modal">閉じる</button>
      </div>
    </div>
  </div>
</div>

    
<div class="container" style="max-width:500px">
<div class="main">
<div class="row my-3 justify-content-between">
<div class="col-12">
 	<span class="h2">
   		@php
   			$ymd = explode("-",$date);
   			echo $ymd[0].'/'.$ymd[1].'/'.$ymd[2];
   		@endphp
   	</span>
</div>
</div>

<table class="table table-bordered table-sm">
<thead class="thead-dark">
<tr><th style="width:22%">開始</th><th style="width:22%">終了</th><th style="width:22%">空き状況</th><th style="width:auto">予約</th></tr>
</thead>
@foreach ($frames as $frame)
        <tr>
        <td>{{ date("H:i",strtotime($frame->start_at)) }}</td>
        <td>{{ date("H:i",(strtotime($frame->start_at)+$frame->length))  }}</td>
        <td>
        	@php 
        		$diff = $frame->vaccine_count - $frame->reservation_count;
        	
        		if ($diff <= 0)
        			echo '×';         
        		else if ($diff < env('RESERVATION_LIMITED_THRESHOLD',10) )
        			echo '△';
        		else
        			echo '○';
        	@endphp
        </td>
        <td><button class="btn btn-primary reserve-btn fit-btn" type="button" data-frame_id="{{$frame->frame_id}}">予約</button></td>
		</tr>
	@endforeach
	</table>
        
</div>
<div class="footer" style="height:10%">
<div class="row my-2 justify-content-end">
			<div class="d-flex">
   				<div class="mx-2 w-30">
   					<button class="btn btn-info fit-btn" id="howtouse-btn" type="button" onclick="location.replace('/calendar')" >カレンダー</button>
    			</div>
    			<div class="mx-2 w-30">
			  		<button class="btn btn-primary fit-btn" id="mypage-btn" type="button" onclick="parent.location.replace('/mypage')">マイページ</button>
 
				</div>
			</div>
</div>
</div>
</div>
@endsection
        
        