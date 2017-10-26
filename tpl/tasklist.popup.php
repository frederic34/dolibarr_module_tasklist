<div id="confirm-add-time"  class="modal" role="dialog">
    <div class="modal-dialog">
	    <div class="modal-content">
	    	<div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal">&times;</button>
		        <h4 class="modal-title">Confirmez le temps passé</h4>
		    </div>
		    <div class="modal-body">
		        <form class="form-inline">
		        	
		        	<div class="form-group">
		              <?php if ($user->rights->tasklist->all->write){ ?>
		              	
		              	 <div class="input-group">
				              <input id="heure" value="" placeholder="H" class="form-control" type="text" /> 
				              <span class="input-group-addon" style="width:50%;" id="heure-addon1">Heure(s)</span>
			             </div>
			             <div class="input-group">
			              <input id="minute" value="" placeholder="m" class="form-control" type="text" /> 
			              <span class="input-group-addon" style="width:50%;" id="minute-addon" >Minute(s)</span>
			             </div>
		              <?php } else{ ?>
		              	
		              	 <div class="input-group ">
				              <input id="heure" value="" placeholder="H" class="form-control" type="text"  disabled="disabled" /> 
				              <span class="input-group-addon" style="width:50%;" id="heure-addon1">Heure(s)</span>
			             </div>
			             <div class="input-group ">
				              <input id="minute" value="" placeholder="m" class="form-control" type="text" disabled="disabled" /> 
				              <span class="input-group-addon" style="width:50%;" id="minute-addon" >Minute(s)</span>
			             </div>
		              	
		              <?php }
		              
		              	$hookmanager->initHooks(array('tasklistcard'));
		              	$reshook = $hookmanager->executeHooks('doActionsCloseTask', $parameters, $object, $action);
		              
		              ?>
		             
		            </div>
		        </form> 
		       </div>
		       <div class="modal-footer">
		       	<a class="btn btn-default" id="valide_popup">Valider</a>
		       	</div>  
	    </div>
    </div>
</div>

<div id="modal-alert"  class="modal" role="dialog">
    <div class="modal-dialog">
	    <div class="modal-content">
	    	<div class="modal-header">
		        <h4 class="modal-title"><?php echo $langs->trans('Info'); ?></h4>
		    </div>
		    <div class="modal-body">
				
	       </div>
	       <div class="modal-footer">
		       	<a class="btn btn-default"  data-dismiss="modal" aria-hidden="true">Ok</a>
	       	</div>  
	    </div>
    </div>
</div>
<script type="text/javascript">
(function() {
  var proxied = window.alert;
  window.alert = function() {
    $("#modal-alert .modal-body").text(arguments[0]);
    $("#modal-alert").modal('show');
  };
})();
</script>