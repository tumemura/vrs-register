@extends('base') 
@section('title','マイページ') 

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

	$('#passport-btn').on('click',function() {
		$('#passportDialog').modal();
	});	


	$('#cancel-btn').on('click',function() {
		$('#yesNoTitle').text('予約取消');
		$('#yesNoMsg').html('全ての予約を取り消します。<br>よろしいでしょうか？');
		$('#yesNoDialog').modal();
		$('#yes-btn').on('click',function(){	
			location.replace('/cancel');
			$('#yes-btn').off('click'); // イベントハンドラーを登録解除
		});
	});

	@if (Session::has('error'))
		$('#errorDialog').modal();
	@endif


	@if (Session::has('message'))
		$('#messageDialog').modal();
	@endif
});

</script>


@endsection

@section('body')




<!-- Modal -->
<div class="modal" id="errorDialog" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="errorDialogModalLabel">エラー</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal-msg">
	  	{!! Session::get('error','') !!}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="close-btn" data-dismiss="modal">閉じる</button>
      </div>
    </div>
  </div>
</div>


<div class="modal" id="messageDialog" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageDialogTitle">情報</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal-msg">
	  {!! Session::get('message','') !!}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="close-btn" data-dismiss="modal">閉じる</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="passportDialog" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">

      <div class="modal-body" id="passportBody">
      	<div class="py-2"></div>
       	<p style="text-align:center"><img src="{{ asset('/img/passport.png') }}" width="100%"></p>
       	<p style="text-align:center"><span class="h3">{{$patient->last_name}}&nbsp;{{$patient->first_name}}</span></p>
       	<table class="table table-sm table-borderless">
		<tbody>
		@foreach ($vaccinations as $item)
			@php
				$vaccination_date = $item->vaccinated_at;
				$data = \DB::table('reservations')
                    ->join('frames', 'frames.frame_id', '=', 'reservations.frame_id')
                    ->where('reservation_id', $item->reservation_id)
                    ->first();
				if ($data != null)
					$vaccination_date = $data->start_at;
			@endphp
			<tr>
				<td>{{ date("Y/m/d",strtotime($vaccination_date)) }}</td>
				<td>{{ $item->vaccine_name }} </td>
			</tr>
		@endforeach
		</tbody>
		</table>
		<div class="py-2" style="text-align:center">
		 <button type="button" class="btn btn-secondary" id="close-btn" data-dismiss="modal">閉じる</button>
		 </div>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="yesNoDialog" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="yesNoTitle"></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body" id="yesNoMsg"></div>
		<div class="modal-footer">
			<button type="button" class="btn btn-outline-primary" id="no-btn" data-dismiss="modal">いいえ</button>
			<button type="button" class="btn btn-success" id="yes-btn" data-dismiss="modal">はい</button>
		</div>
    </div>
  </div>
</div>

<style>

body {
	height: 100%;
}

.modal-body {
    padding: 0 1rem;
}

.modal-content {
    justify-content: flex-end;
}


main {
	margin-bottom: auto;
}

footer {
	height: 60px;
	background-color: #fff;
	bottom: 0;
    padding: 5px;
	position: sticky;
}

.container {
	display: flex;
  flex-direction: column;
  min-height: 100vh;
}


</style>

<div class="container">

	<main>
		<div class="row my-5 justify-content-center">
			<div class="col-auto">
				<span class="h3">マイページ</span>
			</div>
		</div>
		<div class="row mb-4 justify-content-center">
			<div class="col-auto">
				<span class="h3">予約情報</span>
			</div>
		</div>		
		<div class="row mb-2">
			<div class="col-12">
				<table class="table table-bordered table-sm">
					<thead>
						<tr>
							<th>予約日時</th>
							<th>ワクチン名</th>
							<th>状態</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($reservations as $reservation)
						<tr>
							<td>
								@php
									$datetime = explode(' ',$reservation->start_at);
									$date = explode('-',$datetime[0]);
									$time = explode(':',$datetime[1]);
									echo $date[0].'/'.$date[1].'/'.$date[2].'<br>';
									echo $time[0].':'.$time[1];							
								@endphp
							</td>
							<td>
							@php
								echo $reservation->vaccine_name;
							@endphp
							</td>
							<td>{{$reservation->status_desc}}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>

		@if (count($vaccine_list)>0)
			@foreach ($vaccine_list as $item)
				<div class="row justify-content-center mt-1">
					<div class="form-group col-lg-6">
							<button class="btn btn-success fit-btn" type="button" onclick="location.href='/calendar/{{$item['vaccine_id']}}';">{{$item['text']}}</button>
					</div>
				</div>
			@endforeach
		@endif	
		@if ($cancellation_possible)
		<div class="row justify-content-center mt-1">
			<div class="form-group col-lg-6">
				<button class="btn btn-info fit-btn" id="cancel-btn" type="button">予約取消</button>
			</div>
		</div>
		@endif
		@if (count($vaccinations) >= 2)
		<div class="row justify-content-center mt-1">
			<div class="form-group col-lg-6">
				<button class="btn btn-success fit-btn" id="passport-btn" type="button">パスポート</button>
			</div>
		</div>
		@endif
		<div class="row justify-content-center mt-3">
			<div class="col text-center" style="font-size:12px;">
				<b>
				接種当日はご来院前に体温を測定していただき、体温が37.5℃以上ある場合は接種をお控えください<br>
				キャンセルはワクチンネットまたはお電話(011-783-5100)で承ります。
				</b>
			</div>
		</div>

	</main>
	<footer>
		<div class="row justify-content-end h-auto align-items-end">
			<div class="d-flex">
				<div class="w-30">
					<button class="btn btn-primary fit-btn" type="button" onclick="location.replace('/logout');">ログアウト</button>
				</div>
			</div>
		</div>
	</footer>	
</div>


@endsection



