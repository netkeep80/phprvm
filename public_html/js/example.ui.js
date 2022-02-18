
// init document
$(document).ready(function()
{
	function PlusBinder()
	{
		$('.el').unbind().bind('click', function(e)
		{
			e.stopPropagation();
			e.preventDefault();
			
			$('.curr_').removeClass('curr_');
			$(this).addClass('curr_');
			
			var id = $(this).attr('ref');
			
			if( '' == id )
			{
				$('.col-right').html('');
				//EditFormInit();
				return;
			}
			
			$.ajax({
				type: "GET", // type of request
				url: '/Entity_PHPView.php',//Edit.php',
				cache: false,
				dataType: "html", // type of response
				data: 'is_ajax=true&id='+id,
				success: function(html) 
				{
					$('.col-right').html(html);
					//EditFormInit();
				}
			});
		});
		
		// binder
		$('.a-tree:not(".binded")').bind('click', function()
		{
			var that = this;
			var id = $(this).attr('ref');
			var data = 'action=getentbysubid&id='+id;
			
			$.ajax({
				type: "POST", // type of request
				url: '/axcall.php',
				cache: false,
				dataType: "json", // type of response
				data: data,
				success: function(json) 
				{
					if ( !json.status )
					{
						alert('error: ' + json.message);
						//todo;
						return;
					}
					
					$(that).html('<b>></b>');
					
					// after ajax on success
					$('#tree-place-'+id).html(json.html);
					
					// here call binder again	
					PlusBinder();
					
					if ( json.message != '' )
					{
						alert('success:' + json.message);
					}
				}
			});
		}).addClass('binded');
	}
	PlusBinder();
	

	// bind some action to form in document. form id="myForm"
	$('#myForm button').bind('click', function()
	{
	
		// we can get all values of form if form have multiply fieds ( inputs, txtareas, selects etc... )
		var data = $('#myForm').serialize();
		
		// or get some single field
		// var data = $('#field1').serialize(); // 
		// or 
		// var data = 'field1=' + $('#field1').val();
		
		// add action in request
		data = data + '&action=add';
				
		// call ajax 
		$.ajax({
			type: "POST", // type of request
			url: '/axcall.php',
			cache: false,
			dataType: "json", // type of response
			data: data,
			success: function(json) 
			{
				if ( !json.status )
				{
					alert('error: ' + json.message);
					//todo;
					return;
				}
				
				if ( json.message != '' )
				{
					alert('success:' + json.message);
				}
				// clear form fields
				$('#myForm input[type="text"]').val('');
				// todo
			}
		});
	});
	
	function EditFormInit()
	{
		// bind some action to form in document. form id="EditForm"
		$('#EditForm button').bind('click', function()
		{
		
			// we can get all values of form if form have multiply fieds ( inputs, txtareas, selects etc... )
			var data = $('#EditForm').serialize();
			
			// or get some single field
			// var data = $('#field1').serialize(); // 
			// or 
			// var data = 'field1=' + $('#field1').val();
			
			// edit action in request
			data = data + '&action=edit';
					
			// call ajax 
			$.ajax({
				type: "POST", // type of request
				url: '/axcall.php',
				cache: false,
				dataType: "json", // type of response
				data: data,
				success: function(json) 
				{
					if ( !json.status )
					{
						alert('error: ' + json.message);
						//todo;
						return;
					}
					
					if ( json.message != '' )
					{
						alert('success:' + json.message);
					}
					// clear form fields
					//$('#EditForm input[type="text"]').val('');
					// todo
				}
			});
		});
	}
	EditFormInit();
	
	// bind some action to form in document. form id="DeleteForm"
	$('.DeleteButton').bind('click', function()
	{
		if ( !confirm('Please Confirm') ) return false;
		
		var that = this, row = $(that).closest('tr');
		
		// we can get all values of form if form have multiply fieds ( inputs, txtareas, selects etc... )
		var data = 'action=delete&EntId='+$(this).attr('ref');
		//alert(data);
		
		// or get some single field
		// var data = $('#field1').serialize(); // 
		// or 
		// var data = 'field1=' + $('#field1').val();
		
		// edit action in request
				
		// call ajax 
		$.ajax({
			type: "POST", // type of request
			url: '/axcall.php',
			cache: false,
			dataType: "json", // type of response
			data: data,
			success: function(json) 
			{
				if ( !json.status )
				{
					alert('error: ' + json.message);
					//todo;
					return;
				}
				$(row).remove();
				
				
				if ( json.message != '' )
				{
					alert('success:' + json.message);
				}
				// clear form fields
				//$('#DeleteForm input[type="text"]').val('');
				// todo
			}
		});
	});
});