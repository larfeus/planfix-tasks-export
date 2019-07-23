;(function($) {
	$(function() {

		$.extend({
			/**
		     * Debounce's decorator
		     * @param {Function} fn original function
		     * @param {Number} timeout timeout
		     * @param {Boolean} [invokeAsap=false] invoke function as soon as possible
		     * @param {Object} [ctx] context of original function
		     */
		    debounce: function(fn, timeout, invokeAsap, ctx) {

		        if(arguments.length == 3 && typeof invokeAsap != 'boolean') {
		            ctx = invokeAsap;
		            invokeAsap = false;
		        }

		        var timer;

		        return function() {

		            var args = arguments;
		            ctx = ctx || this;

		            invokeAsap && !timer && fn.apply(ctx, args);

		            clearTimeout(timer);

		            timer = setTimeout(function() {
		                invokeAsap || fn.apply(ctx, args);
		                timer = null;
		            }, timeout);

		        };
		    }
		});

		$('.select2').select2();

		$('[task-search]').each(function() {

			var $inputSearch = $(this);
			var $targetContainer = $('[task-container="' + $inputSearch.attr('task-search') + '"]');
			var $targetInputs = $('input', $targetContainer);
			var $targetGroups = $('[task-item]', $targetContainer);
			var $targetCollapses = $('[task-collapse]', $targetContainer);
			var previousValue = $inputSearch.val() || '';
			var promise = Promise.resolve();

			$inputSearch.on('keydown', $.debounce(function() {

				do {
					var searchValue = $(this).val() || '';

					if (searchValue == previousValue) {
						break;
					}

					previousValue = searchValue;

					// скрываем списки
					$targetCollapses.removeClass('show');

					// отображаем все задачи и списки их подзадач
					if (searchValue == '') {
						$targetGroups.removeClass('d-none');
						$targetCollapses.removeClass('d-none');
						break;
					}

					// помечаем задачи, которые соответствуют поиску
					$targetGroups.each(function() {
						var $self = $(this);
						var input = $('input', $self);
						var isMatched = $self.text().toLowerCase().indexOf(searchValue.toLowerCase()) > -1;
						$self[isMatched ? 'removeClass' : 'addClass']('d-none');
						if (!isMatched) {
							input.prop('checked', false);
						}
					});

					// отображаем списки их подзадачи
					$targetGroups.filter(':not(.d-none)').each(function() {
						var $self = $(this);
						var $collapse = $self.next('[task-collapse]');
						$collapse.find('[task-item]').removeClass('d-none');
						$collapse.removeClass('d-none');
						$self.closest('[task-collapse]').each(function() {
							$(this).prev('[task-item]').removeClass('d-none');
							$(this).removeClass('d-none').addClass('show');
						});
					});
				} while(false);

				$targetContainer.children('[task-item]:not(.d-none)').each(function(index) {
					if (index >= 15) {
						$(this).addClass('d-none');
						$(this).next('[task-collapse]').addClass('d-none');
					}
				});

			}, 500)).trigger('keydown');
		});
	});
})(jQuery);