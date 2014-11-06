<?php
class Pass extends AppModel{
      public $belongsTo = array('Player','Game','Shot');
}
?>