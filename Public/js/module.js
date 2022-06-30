/**
 * Module's JavaScript.
 */

function clickDeleteNote(thread_id){
  showModalConfirm(Lang.get('messages.confirm_delete_note'), 'dn-delete-note', {
    on_show: function(modal) {
      modal.children().find('.dn-delete-note:first').click(function() {
        modal.modal('hide')
        fsAjax(
          {thread_id},
          laroute.route('deletenotes.delete', {thread_id: 139360}),
          function(response) {
            showAjaxResult(response)
            $('#thread-'+thread_id).remove()
          }
        )
      })
    }
  }, Lang.get("messages.delete"))
}