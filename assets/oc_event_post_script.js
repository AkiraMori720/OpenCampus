jQuery(document).ready(function ($) {
	$( "#upload_date").datepicker({
		dateFormat: 'yy/mm/dd'
	});


	$('input[type=radio][name="event_option_bus_route"]').change(function(){
		if(this.value == "off"){
			$("#post_field_bus_routes_list").hide();
		}
		else{
			$("#post_field_bus_routes_list").show();
		}
	});


	$('input[type=checkbox][name="post_field_bus_route_item"]').change(function(){
		var bus_routes = [];
		if($('input[name=post_field_bus_route]').val() != "")
			bus_routes = $('input[name=post_field_bus_route]').val().split(',');
		
		if(this.checked){
			bus_routes.push(this.value);
		}
		else{
			const index = bus_routes.indexOf(this.value);
			if (index > -1) {
				bus_routes.splice(index, 1);
			}
		}
		$('input[name=post_field_bus_route]').val(bus_routes.join(','));
	});

	$('.check_box_field span.event_option_check_label').click(function(){
		var $input_option = $(this).closest('div').find('input[type="radio"]');
		if($input_option.attr('checked') != 'checked')
			$input_option.attr('checked', 'checked');
		if($input_option[0].name == 'event_option_bus_route')
		{
			if($input_option[0].value == "off"){
				$("#post_field_bus_routes_list").hide();
			}
			else{
				$("#post_field_bus_routes_list").show();
			}
		}

		if($input_option[0].className == "block_media" || $input_option[0].className == "block_text")
		{
			var $tr = $(this).closest(".data-table-index-tr").next();
			if($input_option[0].className == "block_media"){
				$tr.find('.block_data_media').show();
				$tr.find('.block_data_text').hide();
				$(this).closest("div.check_box_field").find('input.block_media').checked = 'checked';
				$(this).closest("div.check_box_field").find('input.block_text').removeAttr('checked');
			}
			else if($input_option[0].className == "block_text")
			{
				$tr.find('.block_data_media').hide();
				$tr.find('.block_data_text').show();
				$(this).closest("div.check_box_field").find('input.block_text').checked = 'checked';
				$(this).closest("div.check_box_field").find('input.block_media').removeAttr('checked');
			}
		}

	});

	// Access Table
	var $ACCESS_TABLE = $('#access_table');
	var $ACCESS_ADD_BTN = $('#access-add-btn');
	var $ACCESS_DATA = $('#access_data');

	init_access_table();
	function init_access_table(){
		var data = $ACCESS_DATA.val();
		if(!data || data === "")
			return;

		var access_data = jQuery.parseJSON(data);
		access_data.forEach(row => {
			var $clone = $ACCESS_TABLE.find('tr.hide_row').clone(true).removeClass('hide_row table-line');
			$clone.find('input.access_label').val(row.name);
			$clone.find('input.access_url').val(row.url);
			$ACCESS_TABLE.find('table').append($clone);
		});
		refreshaccesstableids();
	}
	
	$('#access_table .table-add').click(function (e) {
		var $clone = $ACCESS_TABLE.find('tr.hide_row').clone(true).removeClass('hide_row table-line');
		$ACCESS_TABLE.find('table').append($clone);
		refreshaccesstableids();
	});

	function refreshaccesstableids(){
		var $rows = $ACCESS_TABLE.find('.table_row .table_id_field');
		for(var i=0; i<$rows.length; i++){
			$rows[i].innerHTML = i;
		}
	};
	
	$ACCESS_ADD_BTN.click(function (e) {
		var $clone = $ACCESS_TABLE.find('tr.hide_row').clone(true).removeClass('hide_row table-line');
		$ACCESS_TABLE.find('table').append($clone);
		refreshaccesstableids();
		e.preventDefault();
	});
	
	$('#access_table .table-remove').click(function () {
		$(this).parents('tr').detach();
		refreshaccesstableids();
	});
	
	// A few jQuery helpers for exporting only
	jQuery.fn.pop = [].pop;
	jQuery.fn.shift = [].shift;
	
	function updateaccessdata() {
	  var $rows = $ACCESS_TABLE.find('tr.table_row:not(:hidden)');
	  var data = [];
	  
	  // Turn all existing rows into a loopable array
	  $rows.each(function () {
		var $td = $(this).find('td:not(.table_id_field) input');
		var h = {};
		
		h['name'] = $td.eq(0).val();   
		h['url'] = $td.eq(1).val();

		data.push(h);
	  });
	  
	  // Output the result
	  $ACCESS_DATA.val(JSON.stringify(data));
	}



	var $image_data_tag = null;
	var file_frame;
	var wp_media_post_id = wp.media.model.settings.post.id;
	var set_to_post_id = 0;

	// Image Upload Event
	$("button.add-image").click(function(e){
		open_media_window(e);
		$image_data_tag = $(this).closest("td").find(".img_data");
		var $slug = $(this).closest("div").find(".main_img_slug");
		if($slug)
			$slug.css('display', 'none');
		
		return false;
	});

	function open_media_window(e) {
		e.preventDefault();

		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			// Set the post ID to what we want
			file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
			// Open frame
			file_frame.open();
			return;
		} else {
			// Set the wp.media post id so the uploader grabs the ID we want when initialised
			wp.media.model.settings.post.id = set_to_post_id;
		}

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: '画像を選択する',
			button: {
				text: '選択',
			},
			multiple: false	// Set to true to allow multiple files to be selected
		});

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			
			// Do something with attachment.id and/or attachment.url here
			$image_data_tag.attr( 'src', attachment.url );
			$( '#image_attachment_id' ).val( attachment.id );
			
			// Restore the main post ID
			wp.media.model.settings.post.id = wp_media_post_id;
		});
		
		// Finally, open the modal
		file_frame.open();
	}
	
	
	// Course Table
	$COURSE_TABLE = $("#course_table");
	$COURSE_ADD_BTN = $("#course-add-btn");
	$COURSE_DATA = $("#course_data");
	$coure_table_index = 0;

	// Course Detail Table
	var course_detail_index = 0;
	
	init_course_table();
	function init_course_table(){
		var data = $COURSE_DATA.val();
		if(!data || data === "")
		{
			coursetableaddrow();
			return;
		}
		var course_data = jQuery.parseJSON($COURSE_DATA.val());
		//console.log(course_data);
		course_data.forEach(row => {
			var $clone = $COURSE_TABLE.find('tr.hide_row.course_table_row').clone(true).removeClass('hide_row');
			$clone.find('img.main_image').attr('src', row.main_img);
			if(row.main_img)
				$clone.find('span.main_img_slug').css('display', 'none');
			$clone.find('input.course_name').val(row.course_name);
			$clone.find('input.course-detail-data[type="hidden"]').val(row.course_detail)
			$clone.find('input.sub_course_data[type="hidden"]').val(row.sub_courses);
			$is_ended = $clone.find('input.is_ended');
			$is_ended[0].checked = row.is_ended === undefined? false: row.is_ended;
			$has_traffic_help = $clone.find('input.has_traffic_help');
			$has_traffic_help[0].checked = row.has_traffic_help === undefined? false: row.has_traffic_help;
			init_course_detail_table($clone.find('div.course_data_table'));
			init_sub_course_table($clone.find('div.sub_course_table'));
			$COURSE_TABLE.find('#course_table_id').append($clone);
		});
		if(course_data.length == 0)
			coursetableaddrow();
		else
			refreshcoursetableids();
	}


	$COURSE_ADD_BTN.click(function (e) {
		coursetableaddrow();
		e.preventDefault();
	});


	$('#course_table .course-add').click(function (e) {
		coursetableaddrow();
	});

	function coursetableaddrow(){
		var $clone = $COURSE_TABLE.find('tr.hide_row.course_table_row').clone(true).removeClass('hide_row');
		$COURSE_TABLE.find('#course_table_id').append($clone);
		refreshcoursetableids();
	}

	$('#course_table .course-remove').click(function () {
		var $remove_tr = $(this).parents('tr');
		$remove_tr.next(".course_table_row").detach();
		$remove_tr.next(".course_table_row").detach();
		$remove_tr.next(".course_table_row").detach();
		$remove_tr.next(".course_table_row").detach();
		$remove_tr.next(".course_table_row").detach();
		$remove_tr.detach();
		refreshcoursetableids();
	});

	function refreshcoursetableids(){
		var $rows = $COURSE_TABLE.find('.table_index_tr .table_index');
		for(var i=0; i<$rows.length; i++){
			$rows[i].innerHTML = i;
		}
	}

	function updatecoursedata(){
		var $rows = $('#course_table tr.table_index_tr:not(:hidden)');
		var data = [];
		
		// Turn all existing rows into a loopable array
		$rows.each(function () {
			var h = {};
			var $main_img_row = $(this);
			var $course_name_row = $(this).next();
			var $course_detail_row = $(this).next().next();
			var $sub_course_row = $(this).next().next().next();
			var $course_is_end_row = $(this).next().next().next().next();
			var $course_has_traffic_help_row = $(this).next().next().next().next().next();
			
			var main_image = $main_img_row.find('.img_data');
			h['main_img'] = main_image[0].src;
			h['course_name'] = $course_name_row.find('input.course_name').val();
			h['course_detail'] = $course_detail_row.find('input.course-detail-data').val();
			h['sub_courses'] = $sub_course_row.find('input.sub_course_data').val();
			var is_ended = $course_is_end_row.find('input.is_ended');
			h['is_ended'] = is_ended[0].checked?1:0;
			var has_traffic_help = $course_has_traffic_help_row.find('input.has_traffic_help');
			h['has_traffic_help'] = has_traffic_help[0].checked?1:0;
		  	data.push(h);
		});
		
		// Output the result
		$COURSE_DATA.val(JSON.stringify(data));
	}

	
	function init_course_detail_table($table){
		var data = $table.find('input.course-detail-data[type="hidden"]').val();
		var course_detail_data = jQuery.parseJSON(data);
		//console.log(course_detail_data);
		course_detail_data.forEach(row => {
			var $clone = $table.find('tr.hide_row.couse_data_table_row').clone(true).removeClass('hide_row');
			var editor_id = "block_data_detail_" + course_detail_index++;
			$clone.find('textarea#block_data_detail').attr('id', editor_id);
			if(row["media"] !== undefined){
				$clone.find('input.block_media').attr('checked', 'checked');
				$clone.find('img.img_data').attr('src', row.media);
			}
			else if(row["text"] !== undefined){
				$clone.find('input.block_media').removeAttr('checked');
				$clone.find('input.block_text').attr('checked', 'checked');
				$clone.find('div.block_data_media').css('display', 'none');
				$clone.find('div.block_data_text').css('display', '');
				$clone.find('.text_data').text(row.text);
			}
			$table.find('table.course_data_table_id').append($clone);
			wp.editor.initialize(editor_id, {
				tinymce: {
					toolbar1:"bold, italic, strikethrough, bullist, numlist, blockquote, hr, alignleft, aligncenter, alignright, link, unlink, wp_more, spellchecker, wp_adv",
					toolbar2:"formatselect, underline, alignjustify, forecolor, pastetext, removeformat, charmap, outdent, indent, undo, redo, wp_help",
				},
				quicktags: {
					"buttons": "strong,em,link,ul,li,code"
				}
			});
		});
		if(course_detail_data.length != 0)
			refreshcoursedatatableids($table);
	}

	$(".post_field_content button.course-data-add-btn").click(function (e) {
		$table = $(this).closest(".course_data_table").find('table');
		cursedatatableaddrow($table);
		e.preventDefault();
	});

	$('#course_data_table .course-data-add').click(function (e) {
		var $table = $(this).closest(".course_data_table_id");
		cursedatatableaddrow($table);
	});

	function cursedatatableaddrow($table){
		var $clone = $table.find('tr.hide_row.couse_data_table_row').clone(true).removeClass('hide_row');
		var editor_id = "block_data_detail_" + course_detail_index++;
		$clone.find('textarea#block_data_detail').attr('id', editor_id);
		$table.append($clone);
		wp.editor.initialize(editor_id, {
			tinymce: {
				toolbar1:"bold, italic, strikethrough, bullist, numlist, blockquote, hr, alignleft, aligncenter, alignright, link, unlink, wp_more, spellchecker, wp_adv",
				toolbar2:"formatselect, underline, alignjustify, forecolor, pastetext, removeformat, charmap, outdent, indent, undo, redo, wp_help",
			},
			quicktags: {
				"buttons": "strong,em,link,ul,li,code"
			}
		});
		refreshcoursedatatableids($table);
	}
	
	$('#course_data_table .course-data-remove').click(function () {
		var $table = $(this).closest(".course_data_table_id");
		var $remove_tr = $(this).closest('tr');
		$remove_tr.next().detach();
		$remove_tr.detach();
		refreshcoursedatatableids($table);
	});
	
	function refreshcoursedatatableids($table){
		var $rows = $table.find('.data-table-index-tr .table_index');
		var block_media_options = $table.find('.data-table-index-tr input.block_media');
		var block_text_options = $table.find('.data-table-index-tr input.block_text');

		for(var i=0; i<$rows.length; i++){
			if(!block_media_options[i].checked && !block_text_options[i].checked) 
				block_media_options[i].checked = 'checked'; 
			$rows[i].innerHTML = i;
		}
	}

	// Media / Text switch
	$("#course_table .course_data_table .data-table-index-tr input.block_media").click(function(){
		var $tr = $(this).closest(".data-table-index-tr").next();
		var $block_text = $(this).closest("div.check_box_field").find('input.block_text');
		if(this.checked){
			$tr.find('.block_data_media').show();
			$tr.find('.block_data_text').hide();
			$block_text.removeAttr('checked');
		}
		$(this).checked = 'checked';
	});
	
	$("#course_table .course_data_table .data-table-index-tr input.block_text").click(function(){
		var $tr = $(this).closest(".data-table-index-tr").next();
		var $block_media = $(this).closest("div.check_box_field").find('input.block_media');
		if(this.checked){
			$tr.find('.block_data_text').show();
			$tr.find('.block_data_media').hide();
			$block_media.removeAttr('checked');
		}
		$(this).checked = 'checked';
	});

	$("#course_table .course_table_row .is_ended_label").click(function(){
		var $td = $(this).closest("td");
		var $is_ended = $td.find('input.is_ended');
		$is_ended[0].checked = !$is_ended[0].checked;
	});

	$("#course_table .course_table_row .has_traffic_help_label").click(function(){
		var $td = $(this).closest("td");
		var $has_traffic_help = $td.find('input.has_traffic_help');
		$has_traffic_help[0].checked = !$has_traffic_help[0].checked;
	});

	setInterval(function(){
		$('#course_table .course_data_table').each(function(index){
			updatecoursedetaildata($(this));
		})
		$('#course_table .sub_course_table').each(function(index){
			updatesubcoursedetaildata($(this));
		})
		updatecoursedata();
		updateaccessdata();
	}, 500);

	function updatecoursedetaildata($tablediv){
		var $rows = $tablediv.find('tr.course_select_data:not(:hidden)');
		var headers = [];
		var data = [];
		
		// Turn all existing rows into a loopable array
		$rows.each(function () {
			var h = {};
			var $media = $(this).find('td div.block_data_media');
			if($media.is(":not(:hidden)")){
				var image_data = $media.find('.img_data');
				h['media'] = image_data[0].src; 
		  	}
		  	else{
				var textdiv = $(this).find('td div.block_data_text textarea.text_data');
				var editorId = textdiv[0].id;
				h['text'] = wp.editor.getContent(editorId);
			}
		  	data.push(h);
		});
		
		$COURSE_DETAIL_DATA = $tablediv.find(".course-detail-data");
		// Output the result
		$COURSE_DETAIL_DATA.val(JSON.stringify(data));
	}

	// Sub Course 
	$(".post_field_content .sub_course_table button.sub-course-add-btn").click(function (e) {
		$table = $(this).closest(".sub_course_table").find('table');
		subcursedatatableaddrow($table);
		e.preventDefault();
	});

	$('#course_table .sub_course_table .sub-course-add').click(function (e) {
		var $table = $(this).closest(".sub_course_table").find('table');
		subcursedatatableaddrow($table);
	});

	function subcursedatatableaddrow($table){
		if(checksubcoursenum($table))
		{
			var $clone = $table.find('tr.hide_row.sub_course_table_row').clone(true).removeClass('hide_row');
			$table.append($clone);
			refreshsubcoursedatatableids($table);
		}
		else{
			var $warning = $table.closest("div.sub_course_table").find('div.sub-notice-warning');
			$warning.removeClass('hidden');
		}
	}

	$('#course_table .sub_course_table a.sub-notice-dismiss').click(function (e) {
		var $warning = $(this).closest('div.sub-notice-warning');
		$warning.addClass('hidden');
	});
	
	$('#course_table .sub_course_table .sub-course-remove').click(function () {
		var $table = $(this).closest(".sub_course_table").find('table');
		var $remove_tr = $(this).closest('tr');
		$remove_tr.detach();
		refreshsubcoursedatatableids($table);
	});

	function checksubcoursenum($table){
		var $rows = $table.find('.sub_course_table_row .table_index');
		// limit sub course number less then 4
		// if($rows.length >= 4)
		// 	return false;
		return true;
	}

	function refreshsubcoursedatatableids($table){
		var $rows = $table.find('.sub_course_table_row .table_index');

		for(var i=0; i<$rows.length; i++){
			$rows[i].innerHTML = i;
		}
	}

	function updatesubcoursedetaildata($tablediv){
		var $rows = $tablediv.find('tr.sub_course_table_row:not(:hidden)');
		var data = [];
		
		// Turn all existing rows into a loopable array
		$rows.each(function () {
			var sub_course_name = $(this).find('td input.sub_course_name').val();
		  	data.push(sub_course_name);
		});
		
		$SUB_COURSE_DATA = $tablediv.find("input.sub_course_data");
		// Output the result
		$SUB_COURSE_DATA.val(JSON.stringify(data));
	}

	function init_sub_course_table($tablediv){
		var data = $tablediv.find('input.sub_course_data[type="hidden"]').val();
		var $table = $tablediv.find('table');
		var sub_course_data = jQuery.parseJSON(data);
		//console.log(sub_course_data);
		if(sub_course_data != null){
			sub_course_data.forEach(row => {
				var $clone = $table.find('tr.hide_row.sub_course_table_row').clone(true).removeClass('hide_row');
				$clone.find('input.sub_course_name').val(row);
				$table.append($clone);
			});
			if(sub_course_data.length != 0)
				refreshsubcoursedatatableids($table);
		}
	}
});
