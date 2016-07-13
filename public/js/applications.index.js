
;(function($) {
	
	function initializeJobsSelectField()
	{
		var selectedValue = null;
		
		var jobs = new Bloodhound({
			name: 'jobs',
			remote: basePath + '/' + lang + '/jobs/typeahead?q=%QUERY',
			valueKey: 'id',
			datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
			queryTokenizer: Bloodhound.tokenizers.whitespace
		});
		
		jobs.initialize();
		
		var filterDisplayText = function(d) {
			return '[' + d.applyId + '] ' + d.title; 
		};
		
		$('#search-applications-form #job-filter').
                add('#filterApplication #job-filter').typeahead(
			{
				highlight: true,
				minLength: 2
			},
			{
				name: 'jobs',
				displayKey: filterDisplayText,
				source: jobs.ttAdapter(),
				templates: {
					suggestion: function(d) {
						return '<p>' + d.title + '<br><small style="white-space:nowrap;">' + d.applyId + ' | ' + d.id + '</small></p>';
					}

				}
			}
		).on('typeahead:selected', function(e, d, n) {
			selectedValue = filterDisplayText(d);
                console.log('#job-filter-value', $('#job-filter-value'));
			$('#job-filter-value').val(d.id);
		}).on('blur', function(e) {
			if (selectedValue != $(this).val()) { 
				$(this).val('');
				selectedValue = '';
				$('#job-filter-value').val('');
			}
		}).on('focus', function (e) {
		    $(this).on('mouseup', function(e) {
		    	e.preventDefault();
		    	$(this).off('mouseup');
		    }).select();
		});
		if ('' != $('#search-applications-form #job-filter').typeahead('val')) {
			selectedValue = $('#search-applications-form #job-filter').typeahead('val');
		}
		
		
		
		$('#search-applications-form').submit(function() {
			$(this).find('#job-filter').prop("disabled", true);
		});
		
	}
	
	$(function() {
		$('#search-applications span').popover();
		initializeJobsSelectField();
	});
	
})(jQuery);

$(document).on('click', '#application-multiple-move', function (event) {
	event.preventDefault();
	$(event.target).trigger('wait.stop');
	var ids = getTableMultiCheckbox(event.target);
	if (ids.length == 0) {
		return;
	}
	var $modal = $('#application-multiple-move-modal'),
		$form = $modal.find('form');
	
	$form.find('input[type=hidden]').remove();
	$.each(ids, function (index, id){
		$form.append('<input type="hidden" name="ids[]" value="' + id + '" >');
	})
	$modal.modal();
});