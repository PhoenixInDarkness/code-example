function submitComment(selector) {
  $(selector).submit(function(event) {
      event.preventDefault();
      var form = $(this).serialize();
      var body = encodeURIComponent($(this).find('.comment-form').html());

      var comment = form + '&text=' +  body;
      var parent = $(this).find('#parent_id').val();

      $.ajax({
        type:"POST",
        url: $(this).attr('action'),
        data: comment,
        success: function(data) {
          if($('#response-area')) {
            $('#response-area').remove();
          }

          if(parent){
            var block = document.getElementById('response-'+parent);
            $('#response-area').remove();
            result = '<ul>'+data+'</ul><div id="response-area"><div>';
            // $(block).add(result);
            block.innerHTML+=result;
          }else{
            var block = document.getElementById('all-comment');
            block.innerHTML+=data+'<div id="response-area"><div>';
            if ($('#none-comment')) {
                $('#none-comment').remove();
            }
            $('.comment-form').html('');
            document.getElementById('comment-submit').disabled=false;
          }

          deletedComment();
          updateComment(".comment_update");
        },
        error: function(xhr, str) {
          console.log(xhr);
          document.getElementById('comment-submit').disabled=false;
          let bodyModal = '';
          if (xhr.responseJSON.errors) {
            $.each(xhr.responseJSON.errors, function(index, value) {
              bodyModal += '<i class="md-close-circle icon-cancel card-link pr-2" style="padding-top: 4px;color:red;"></i>'+value+'<br>';
            });
          }

          let errorModal = new modalW2({
            title: 'Ошибка публикации',
            type: 'alert',
            buttonCancel: 'Ок',
            body: bodyModal,
          });
          errorModal.drawWindow();
          errorModal.show();

          deletedComment();
          updateComment(".comment_update");
        },
      });
    });
}
