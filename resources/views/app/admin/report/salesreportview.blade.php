@extends('app.admin.layouts.default')
{{-- Web site Title --}}
@section('title') {{{ $title }}} :: @parent @stop
@section('styles')
<style type="text/css">
.invoice>div:not(.invoice-footer) {
    margin-bottom: 43px;
}
.invoice-price .invoice-price-right {
    padding: 3px;
}

</style>
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
<link rel="stylesheet"
    href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">
@endsection

{{-- Content --}}
@section('main')
 <div class="invoice">
     <!--  <div class="invoice-company">
         <span class="pull-right hidden-print"> 

             <a href="javascript:;" onclick="window.print()" class="btn btn-sm btn-success m-b-10"><i class="fa fa-print m-r-5"></i>  {{trans('report.print')}}</a>
         </span>     </div> -->
         <div class="invoice-header">
        <div class="invoice-from">
            <address class="m-t-5 m-b-5">
                <strong>{{$app->company_name}}</strong><br>
                            {{$app->company_address}}<br>
                            email: {{$app->email_address}}
                </address>
        </div>
      <!--   <div class="invoice-date">
            <div class="date m-t-5">{{ date('F d, Y') }}</div>
            <div class="invoice-detail">
                Joining repot from August 19, 2015 to  October 19, 2015
            </div>
        </div> -->
    </div>


    <div class="invoice-content">
        <div class="table-responsive">
            <table class="table table-invoice" id = "table">
                <thead>
                    <tr>
                        <th>{{trans('report.no')}}</th>
                        <th>{{trans('report.username')}}</th>
                        <th>{{trans('report.firstname')}}</th>   
                        <th>{{trans('report.last_name')}}</th>   
                        <th>{{trans('report.email')}}</th>  
                         <th>{{trans('report.amount')}}</th>
                        <th>{{trans('report.date')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportdata as $key=> $report) 
                    <tr>
                        <td>{{ $key +1 }}</td>
                        <td>{{$report->username}}</td>
                        <td>{{$report->name}}</td>
                        <td>{{$report->lastname}}</td>
                        <td>{{$report->email}}</td>
                        <td>{{$currency_sy}} {{$report->amount}}</td>
                        <td>{{ date('d M Y H:i:s',strtotime($report->created_at))}}</td>
                    </tr>
                    @endforeach   
                </tbody>
            </table>
        </div>
            <div class="invoice-price">                       
                <div class="invoice-price-right col-sm-offset-6">
                    {{trans('report.total_amount')}} {{$currency_sy}} {{round($total_amount,2)}}
                </div>
            </div>
    </div>
    <div class="invoice-footer text-muted">
            <p class="text-center m-b-5">
            {{trans('report.thank_you_for_your_business')}}
        </p>
    </div>
</div>             

@endsection
@section('scripts') @parent
  <script  src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.flash.min.js"></script>
<script  src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/pdfmake.min.js"></script>
<script  src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/vfs_fonts.js"></script>
<script  src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>
<script  src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function() {
        App.init();                 
    });
</script>
<script>
    $(document).ready(function() {
        $('#table').DataTable( {
            dom: "<'row'<'col-sm-6'l><'col-sm-6'fr>>" +
                 "<'row'<'col-sm-12't>>" +
                 "<'row'<'col-sm-2'i><'col-sm-5'<'pull-left'p>><'col-sm-5'<'pull-right'B>> >" ,
        language: {
            paginate: {
                next: '<i class="glyphicon glyphicon-chevron-right">',
                previous: '<i class="glyphicon glyphicon-chevron-left">', 
            }
        },
        buttons: [        
        
          { "extend": 'pdf', 
          "pageSize":'A3',
          "orientation":'landscape',
          "text":'<span class="fa fa-print"> PDF</span>',
          "className": 'btn  btn-xs  btn-primary paginate_button ' },

         { "extend": 'csv', 
           "text":'<span class="fa fa-file-excel-o"> CSV</span>',
           "className": 'btn  btn-xs  btn-primary paginate_button  '
        },
         { "extend": 'excel', 
          "text":'<span class="fa fa-file-excel-o"> EXCEL</span>',
          "className": 'btn  btn-xs  btn-primary paginate_button ' },
         
        ] 
    } );
} );
 </script>
 @endsection