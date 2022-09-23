@extends('base')


@section('title','予約')



@section('css')
<link href="{{ asset('/css/fullcalendar/main.css') }}" rel='stylesheet'>
<link href="{{ asset('/css/style.css') }}" rel='stylesheet'>
@component('bootstrap_css') 
@endcomponent 
@endsection

@section('script')
<script src="{{ asset('/js/jquery/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('/js/fullcalendar/main.js') }}"></script>

@component('bootstrap_script') 
@endcomponent 


<script>

function formatDate(dt) {
	  var y = dt.getFullYear();
	  var m = ('00' + (dt.getMonth()+1)).slice(-2);
	  var d = ('00' + dt.getDate()).slice(-2);
	  return (y + '-' + m + '-' + d);
	}
let dateHash = {};
var calendar = null;
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {

        buttonText: {
            today:    '今日',
            month:    '月',
            week:     '週',
            day:      '日',
            
        },
        fixedWeekCount: false,
        initialDate: '{{$start_date}}',
	  	 datesSet: function(info) {
	 			var ds = info.end;
	 			ds.setMonth(ds.getMonth() - 1);
	             var y = ds.getFullYear();
	             var m = ('00' + (ds.getMonth()+1)).slice(-2);
	             var d = ('00' + ds.getDate()).slice(-2);
	 			var startDate = y + '-' + m + '-' + d;
	             
	             $.ajax({
	                 url: '/calendar/start/'+startDate,
	                 type:'GET'
	             });
	        },
        timeZone: 'Asia/Tokyo',
        headerToolbar: {
            left: 'prev today',
            center: 'title',
            right: 'next',
        },
        // 最初の曜日
        firstDay: 0, // 0:日曜日
        // 土曜、日曜を表示
        weekends: true,
        // 週数を表示
        weekNumbers: false,
        locale: 'ja',
        height: '90%',
        
 
        // 日を取り除く
        dayCellContent: function (e) {
            e.dayNumberText = e.dayNumberText.replace('日', '');
        },
        selectable: false,
        dateClick: function(info) {
            let date = info.dateStr;
            if (dateHash[date]) {
	           location.href='/frame/{{$vaccine_id}}/'+date;
            }
       	},
        eventClick: function(info) {
        	 location.href='/frame/{{$vaccine_id}}/'+formatDate(info.event.start);
          }
        });
        let prev = '';
        let index = 0;
        let diff = 0;
        let mark = '';

        @foreach ($summary as $item)
    
    		diff = {{$item->total}} - {{$item->used}};
			mark = '';
			if (diff <= 0)
				mark = '×';
			else if (diff < {{env('RESERVATION_LIMITED_THRESHOLD',10)}} ) {
				dateHash['{{$item->date}}'] = 1;
				mark = '△';
			} else {
				dateHash['{{$item->date}}'] = 1;
				mark = '○';
			}                 
            calendar.addEvent({
                id: '{{$item->date}}',
                title: mark,
                start: '{{$item->date}}',

        	});
    	       
       @endforeach
                        
       calendar.render();
       
});

$(function() { 	    

	$('#howtouse-btn').on('click',function() {
		$('#howToUseDialog').modal();   
	});
});

</script>

@endsection
    
@section('style')
    <style>
    .fc .fc-toolbar-title {
        font-size: 1em;
        margin: 0;
    }
    
    .fc .fc-button {
        font-size: 0.5em;
    }
    
    .fc-event-title-container {
        text-align: center;
    }
    
    .fc-event-title {
         font-size: 1.5em;
     }
     
     a {
        color: black;
     }
    
</style>
    
@endsection
    
@section('body')

<div class="modal" id="howToUseDialog" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="howToUseTitle">予約日の選択</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="howToUseMsg">
    <div class="row mb-4 justify-content-center">
		<div class="col-auto">
					<table class="table table-bordered table-sm">
			<colgroup class="basic_information" span="1" width="50px"></colgroup>
			<tbody>
				<tr><td>無印</td><td>予約不可（非接種日）</td></tr>
				<tr><td>×印</td><td>予約不可（満員）</td></tr>
				<tr><td>△印</td><td>予約可能（残りわずか）</td></tr>
				<tr><td>○印</td><td>予約可能</td></tr>
			</tbody>
			</table>
			<p>ご希望の日にちをタップ又はクリックで選択した後、時間の指定ができます。</p>
			<p>月の変更は画面上部の左右の矢印でできます。</p>
		</div>
	</div>
      <div class="modal-footer">
	　   <button type="button" class="btn btn-secondary" id="close-btn" data-dismiss="modal">閉じる</button>
      </div>
    </div>
  </div>
</div>
</div>

<style>

.container {
    padding-top: 15px;
}

</style>

<div class="container">
<div id='calendar'></div>
<div class="row my-2 justify-content-end">
			<div class="d-flex">
   				<div class="mx-2 w-30">
   					<button class="btn btn-info fit-btn" id="howtouse-btn" type="button" >操作方法</button>
    			</div>
    			<div class="mx-2 w-30">
			  		<button class="btn btn-primary fit-btn" id="mypage-btn" type="button" onclick="parent.location.replace('/mypage')">マイページ</button>
 
				</div>
			</div>
</div>
</div>		
@endsection
    