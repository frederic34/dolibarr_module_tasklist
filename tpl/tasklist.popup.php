<div data-role="panel" id="confirm-add-time" data-display="push" data-theme="b">
    <div class="panel-content">
        <form>
             <a data-rel="close" class="ui-btn ui-icon-delete ui-btn-icon-left">Close panel</a>
              <h3>Temps passé</h3>
              <?php if ($user->rights->tasklist->all->write){ ?>
              <input id="heure" value="" placeholder="H" data-theme="c" type="text" style="width: 35px;" > h 
              <input id="minute" value="" placeholder="m" data-theme="c" type="text" style="width: 35px;" > m<br /> <br />
              <?php } else{ ?>
              <input id="heure" value="" placeholder="H" data-theme="c" type="text" style="width: 35px;" disabled="disabled"> h 
              <input id="minute" value="" placeholder="m" data-theme="c" type="text" style="width: 35px;" disabled="disabled"> m<br /> <br />
              <?php } ?>
             
              <a class="ui-btn ui-icon-check ui-btn-icon-right" id="valide_popup">Valider</a>
              
           
        </form>   
    </div>
</div>

<div data-role="panel" id="select-user" data-display="push" data-theme="b" data-position="right">
    <div class="panel-content">
        <form>
              <a data-rel="close" class="ui-btn ui-icon-delete ui-btn-icon-left">Close panel</a>
              <h3>Sélection utilisateur</h3>
              <?php
              $sql = "SELECT rowid, lastname, firstname 
				   FROM ".MAIN_DB_PREFIX."user 
				   WHERE statut = 1
				ORDER BY lastname ASC";
				$Tab = $PDOdb->ExecuteAsArray($sql);
				$TUser = array();
				
				$TUser[-1] = 'Tous';
				foreach($Tab as &$res){
					$TUser[$res->rowid] = $res->lastname.' '.$res->firstname;
				}
				
				
				?><ul id="list-user" data-role="listview" data-inset="true" data-filter="true" data-filter-placeholder="Utilisateur"><?php
				
				   foreach($TUser as $id=>$user) {
				       
			           print '<li><a href="javascript:selectUser('.$id.');">'.$user.'</a></li>';
			           
				   }
				
				?></ul>
              
           
        </form>   
    </div>
</div>