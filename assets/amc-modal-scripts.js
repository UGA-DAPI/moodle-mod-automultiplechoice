

jQuery(document).ready(function(){
    
    console.log('amc modal scripts');
    
        var modal = jQuery('#amcModal');
    
        console.log('modal ?', modal);
    
        //modal.modal('show');
    
   /* $('#amcModal').on('show.bs.modal', function (event) {
        //var button = $(event.relatedTarget); // Button that triggered the modal
        console.log('modal show');    
    });
    
    $('#amcModal').on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        console.log('modal shown');    
    });    
    
    $('#amcModal').on('hide.bs.modal', function (event) {
        console.log('modal hide');    
    });    
    
    $('#amcModal').on('hidden.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        console.log('modal hidden');    
    });    
    
    $('#amcModal').on('loaded.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        console.log('modal loaded');    
    });*/

    $('#amcModal').on('shown.bs.modal', function (e) { 
        console.log('hidden delegate');
    });


    /*$('body').on('hidden.bs.modal', '#amcModal', function (e) { 
        console.log('hidden delegate');
     });

     $('body').on('shown.bs.modal', '#myModal', function (e) {
        console.log('shown delegate');
    });
    $('body').on('hidden.bs.modal', '#myModal', function (e) { 
        console.log('hidden delegate');
     });
    
     $(".modal").on("shown.bs.modal", function() {
        console.log("Event on .modal: show");
      });*/
   
});


   



