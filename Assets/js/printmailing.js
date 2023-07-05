Mautic.printmailingOnLoad = function (container) {
	var prefix = 'trigger_campaign';
	var parent = mQuery('.dynamic-content-variable, .dwc-variable');
	if (parent.length) {
		prefix = parent.attr('id');
	}

	if (mQuery('#' + prefix + '_variables').length) {
		mQuery('#available_variables').on('change', function () {
			if (mQuery(this).val()) {
				Mautic.addTriggerDialogVariable(mQuery(this).val(), mQuery('option:selected', this).data('field-object'));
				mQuery(this).val('');
				mQuery(this).trigger('chosen:updated');
			}
		});

		mQuery('#' + prefix + '_variables .remove-selected').each(function (index, el) {
			mQuery(el).on('click', function () {
				mQuery(this).closest('.panel').animate(
					{'opacity': 0},
					'fast',
					function () {
						mQuery(this).remove();
						Mautic.reorderTriggerCampaignVariables();
					}
				);

				if (!mQuery('#' + prefix + '_variables li:not(.placeholder)').length) {
					mQuery('#' + prefix + '_variables li.placeholder').removeClass('hide');
				} else {
					mQuery('#' + prefix + '_variables li.placeholder').addClass('hide');
				}
			});
		});

		var bodyOverflow = {};
		mQuery('#' + prefix + '_variables').sortable({
			items: '.panel',
			helper: function (e, ui) {
				ui.children().each(function () {
					if (mQuery(this).is(":visible")) {
						mQuery(this).width(mQuery(this).width());
					}
				});

				// Fix body overflow that messes sortable up
				bodyOverflow.overflowX = mQuery('body').css('overflow-x');
				bodyOverflow.overflowY = mQuery('body').css('overflow-y');
				mQuery('body').css({
					overflowX: 'visible',
					overflowY: 'visible'
				});

				return ui;
			},
			scroll: true,
			axis: 'y',
			stop: function (e, ui) {
				// Restore original overflow
				mQuery('body').css(bodyOverflow);

				// First in the list should be an "and"
				ui.item.find('select.glue-select').first().val('and');

				Mautic.reorderTriggerCampaignVariables();
			}
		});

	}
};

Mautic.reorderTriggerCampaignVariables = function () {
	// Update the filter numbers sot that they are ordered correctly when processed and grouped server side
	var counter = 0,
		prefix = 'trigger_campaign',
		parent = mQuery('.dynamic-content-variable, .dwc-variable');

	if (parent.length) {
		prefix = parent.attr('id');
	}

	mQuery('#' + prefix + '_variables .panel').each(function () {
		mQuery(this).find('[id^="' + prefix + '_variables_"]').each(function () {
			var id = mQuery(this).attr('id'),
				name = mQuery(this).attr('name'),
				suffix = id.split(/[_]+/).pop();

			if (prefix + '_variables___name___variable' === id) {
				return true;
			}

			var newName = prefix + '[variables][' + counter + '][' + suffix + ']';
			if (typeof name !== 'undefined' && name.slice(-2) === '[]') {
				newName += '[]';
			}

			mQuery(this).attr('name', newName);
			mQuery(this).attr('id', prefix + '_variables_' + counter + '_' + suffix);

			// Destroy the chosen and recreate
			if (mQuery(this).is('select') && suffix === "variable") {
				Mautic.destroyChosen(mQuery(this));
				Mautic.activateChosenSelect(mQuery(this));
			}
		});

		++counter;
	});

	mQuery('#' + prefix + '_variables .panel-heading').removeClass('hide');
	mQuery('#' + prefix + '_variables .panel-heading').first().addClass('hide');
};

Mautic.addTriggerDialogVariable = function (elId, elObj) {
	var variableId = '#available_' + elObj + '_' + elId,
		variableOption = mQuery(variableId),
		label = variableOption.text(),
		variableNum = parseInt(mQuery('.available-variables').data('index')),
		prototypeStr = mQuery('.available-variables').data('prototype'),
		fieldType = variableOption.data('field-type'),
		fieldObject = variableOption.data('field-object');

	mQuery('.available-variables').data('index', variableNum + 1);

	prototypeStr = prototypeStr.replace(/__name__/g, variableNum);
	prototypeStr = prototypeStr.replace(/__label__/g, label);

	// Convert to DOM
	prototype = mQuery(prototypeStr);

	var prefix = 'trigger_campaign',
		parent = mQuery(variableId).parents('.dynamic-content-variable, .dwc-variable');

	if (parent.length) {
		prefix = parent.attr('id');
	}

	var variableBase = prefix + '[variables][' + variableNum + ']',
		variableIdBase = prefix + '_variables_' + variableNum + '_';

	if (mQuery('#' + prefix + '_variables div.panel').length === 0) {
		// First filter so hide the glue footer
		prototype.find(".panel-heading").addClass('hide');
	}

	if (fieldObject === 'company') {
		prototype.find('.object-icon').removeClass('fa-user').addClass('fa-building');
	} else {
		prototype.find('.object-icon').removeClass('fa-building').addClass('fa-user');
	}

	prototype.find('.inline-spacer').append(fieldObject);

	prototype.find('a.remove-selected').on('click', function () {
		mQuery(this).closest('.panel').animate(
			{'opacity': 0},
			'fast',
			function () {
				mQuery(this).remove();
			}
		);
	});

	prototype.find("input[name='" + variableBase + "[field]']").val(elId);
	prototype.find("input[name='" + variableBase + "[type]']").val(fieldType);
	prototype.find("input[name='" + variableBase + "[object]']").val(fieldObject);

	prototype.appendTo('#' + prefix + '_variables');

	var variable = mQuery('#' + variableIdBase + 'variable');
	variable.attr('type', fieldType);
};
