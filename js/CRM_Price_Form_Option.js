(function(ts) {
  CRM.$(function($) {

    var discountaddlregIsActiveChange = function discountaddlregIsActiveChange() {
      if(
        $('input#discountaddlreg_is_active').is(':checked') ||
        // Support 'View' page by checking for value of hidden field.
        ($('input[type="hidden"]#discountaddlreg_is_active').val() == 1)
      ) {
        $('input#discountaddlreg_is_active').closest('tr').nextAll('tr').show();
      }
      else {
        $('input#discountaddlreg_is_active').closest('tr').nextAll('tr').hide();
      }
    };

    // Give the bhfe elements table an id so we can handle it later.
    $('input#discountaddlreg_is_active').closest('table').attr('id', 'bhfe_table');
    $('table#bhfe_table').removeClass('form-layout-compressed');
    $('table#bhfe_table').addClass('form-layout');
    $('table#bhfe_table td.nowrap').removeClass('nowrap');

    var mainTable = $('input#label').closest('table');

    // Insert a fieldset after mainTable, and move bhfe table into that.
    mainTable.after('<fieldset id="discountaddlreg_fieldset"><legend>' + ts('Discounts for additional participants') + '</legend></fieldset>');

    myTable = mainTable.clone();
    myTable.attr('id', 'discountaddlreg_table');
    myTable.find('tbody').empty();
    $('fieldset#discountaddlreg_fieldset').append(myTable);
    for (var i in CRM.vars.discountaddlreg.fieldNames) {
      myTable.find('tbody').append($('table#bhfe_table').find('label[for="' + CRM.vars.discountaddlreg.fieldNames[i] +'"]').closest('tr'));
    }

    $('input#discountaddlreg_is_active').change(discountaddlregIsActiveChange);
    discountaddlregIsActiveChange();

    // Append any descriptions for bhfe fields.
    for (var d in CRM.vars.discountaddlreg.descriptions) {
      $('input#' + d + ', select#' + d).after('<div class="description" id="' + d + '-description">'+ CRM.vars.discountaddlreg.descriptions[d] +'</div>');
    }

  });
}(CRM.ts('com.joineryhq.discountaddlreg')));