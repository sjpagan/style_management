(function ($, Drupal) {
  function getIcon(values){
    html;
    if((values.from_file != '') && (values.from_config != '')){
      html = '<i class="sm-icon-override"></i>';
    }


    if((values.from_file == '') && (values.from_config != '')){
      html = '<i class="sm-icon-only-config"></i>';
    }

    if((values.from_file != '') && (values.from_config == '')){
      html = '<i class="sm-icon-only-file"></i>';
    }

    return html;
  }




  Drupal.behaviors.var_edit = {
    attach: function (context, settings) {
      $('.js-variables-editor-wrapper', context).once('processed').each(function ($index) {
        let varables = JSON.parse($(this).find('input').val());
        wrapper = $(this);

        odd_even = 'odd';
        $.each(varables, function(var_name, values){




          html ='<div class="item '+ odd_even + '" style="border: 1px solid red;">';

          html +='<div class="icon">';
          html += getIcon(values);
          html +='</div>';

          html +='<div class="varialbe_name">';
          html += var_name;
          html +='</div>';

          html +='<div class="from_file">';
          html += values.from_file;
          html +='</div>';

          html +='<div class="from_config">';
          html += values.from_config;
          html +='</div>';

          html +='</div>';

          $(wrapper).find('.sm-body').append(html);
          odd_even = (odd_even == 'odd') ? 'even' : 'odd';
        });
      });
    }
  };
})(jQuery, Drupal);
