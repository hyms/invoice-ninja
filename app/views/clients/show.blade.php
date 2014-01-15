@extends('header')

@section('content') 
	
	
	<div class="pull-right">
		{{ Former::open('clients/bulk')->addClass('mainForm') }}
		<div style="display:none">
			{{ Former::text('action') }}
			{{ Former::text('id')->value($client->public_id) }}
		</div>

		{{ DropdownButton::normal('Edit Client',
			  Navigation::links(
			    [
			      ['Edit Client', URL::to('clients/' . $client->public_id . '/edit')],
			      [Navigation::DIVIDER],
			      ['Archive Client', "javascript:onArchiveClick()"],
			      ['Delete Client', "javascript:onDeleteClick()"],
			    ]
			  )
			, ['id'=>'normalDropDown'])->split(); }}

		{{ DropdownButton::primary('Create Invoice',
			  Navigation::links(
			    [
			    	['Create Invoice', URL::to('invoices/create/' . $client->public_id )],
			     	['Enter Payment', URL::to('payments/create/' . $client->public_id )],
			     	['Enter Credit', URL::to('credits/create/' . $client->public_id )],
			    ]
			  )
			, ['id'=>'primaryDropDown'])->split(); }}

	    {{ Former::close() }}
		
	</div>

	<h2>{{ $client->getDisplayName() }}</h2>
	@if ($client->last_login > 0)
	<h3 style="margin-top:0px"><small>		
		Last logged in {{ Utils::timestampToDateTimeString(strtotime($client->last_login)); }}
	</small></h3>
	@endif

	<div class="row">

		<div class="col-md-3">
			<h3>Details</h3>
		  	<p>{{ $client->getAddress() }}</p>
		  	<p>{{ $client->getPhone() }}</p>
		  	<p>{{ $client->getNotes() }}</p>
		  	<p>{{ $client->getIndustry() }}</p>
		  	<p>{{ $client->getWebsite() }}</p>
		  	<p>{{ $client->payment_terms ? "Payment terms: Net " . $client->payment_terms : '' }}</p>
		</div>

		<div class="col-md-3">
			<h3>Contacts</h3>
		  	@foreach ($client->contacts as $contact)		  	
		  		{{ $contact->getDetails() }}		  	
		  	@endforeach			
		</div>

		<div class="col-md-6">
			<h3>Standing</h3>
			<h3>{{ Utils::formatMoney($client->paid_to_date, $client->currency_id); }} <small>Paid to Date USD</small></h3>	    
			<h3>{{ Utils::formatMoney($client->balance, $client->currency_id); }} <small>Balance USD</small></h3>
		</div>
	</div>

	<p>&nbsp;</p>
	
	<ul class="nav nav-tabs nav-justified">
		{{ HTML::tab_link('#activity', 'Activity', true) }}
		{{ HTML::tab_link('#invoices', 'Invoices') }}
		{{ HTML::tab_link('#payments', 'Payments') }}			
		{{ HTML::tab_link('#credits', 'Credits') }}			
	</ul>

	<div class="tab-content">

        <div class="tab-pane active" id="activity">

			{{ Datatable::table()		
		    	->addColumn('Date', 'Message', 'Balance', 'Adjustment')       
		    	->setUrl(url('api/activities/'. $client->public_id))    	
		    	->setOptions('sPaginationType', 'bootstrap')
		    	->setOptions('bFilter', false)
		    	->render('datatable') }}

        </div>

		<div class="tab-pane" id="invoices">

			@if ($hasRecurringInvoices)
				{{ Datatable::table()		
			    	->addColumn('How Often', 'Start Date', 'End Date', 'Invoice Total')       
			    	->setUrl(url('api/recurring_invoices/' . $client->public_id))    	
			    	->setOptions('sPaginationType', 'bootstrap')
			    	->setOptions('bFilter', false)
			    	->render('datatable') }}
			@endif

			{{ Datatable::table()		
		    	->addColumn('Invoice Number', 'Invoice Date', 'Invoice Total', 'Balance Due', 'Due Date', 'Status')       
		    	->setUrl(url('api/invoices/' . $client->public_id))    	
		    	->setOptions('sPaginationType', 'bootstrap')
		    	->setOptions('bFilter', false)
		    	->render('datatable') }}
            
        </div>
        <div class="tab-pane" id="payments">

	    	{{ Datatable::table()		
				->addColumn('Transaction Reference', 'Method', 'Invoice', 'Payment Amount', 'Payment Date')       
				->setUrl(url('api/payments/' . $client->public_id))    	
				->setOptions('sPaginationType', 'bootstrap')
				->setOptions('bFilter', false)
				->render('datatable') }}
            
        </div>
        <div class="tab-pane" id="credits">

	    	{{ Datatable::table()		
				->addColumn('Credit Amount', 'Credit Date', 'Private Notes')       
				->setUrl(url('api/credits/' . $client->public_id))    	
				->setOptions('sPaginationType', 'bootstrap')
				->setOptions('bFilter', false)
				->render('datatable') }}
            
        </div>
    </div>
	
	<script type="text/javascript">

	$(function() {
		$('#normalDropDown > button:first').click(function() {
			window.location = '{{ URL::to('clients/' . $client->public_id . '/edit') }}';
		});
		$('#primaryDropDown > button:first').click(function() {
			window.location = '{{ URL::to('invoices/create/' . $client->public_id ) }}';
		});
	});

	function onArchiveClick() {
		$('#action').val('archive');
		$('.mainForm').submit();
	}

	function onDeleteClick() {
		if (confirm('Are you sure you want to delete this client?')) {
			$('#action').val('delete');
			$('.mainForm').submit();
		}		
	}

	</script>

@stop