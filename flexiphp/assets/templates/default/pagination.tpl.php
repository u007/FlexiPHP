<?

$iPage 				= isset($vars["page"]) ? $vars["page"] : 1;
$iRowPerPage 	= isset($vars["rowsperpage"]) ? $vars["rowsperpage"] : 10;
$iMaxRows			= isset($vars["max"]) ? $vars["max"] : 0;

$iActualPageCount		= ceil($iMaxRows / $iRowPerPage);

$params				= isset($vars["params"]) ? $vars["params"] : array();

$sURL					= isset($vars["url"]) ? $vars["url"] : "[url]";


$iC = $iPage - 10 > 0 ? $iPage - 10: 1;
$iPageCount = $iActualPageCount > $iPage + 10 ? $iPage + 10 : $iActualPageCount;

?>

<div class="pagination" style="width:100%; padding-top: 15px">
    
    <div>
      
        <!-- First page link -->
        <?php if ($iMaxRows > 0 && $iPage > 1): ?>
              <a href='<?=str_replace("[url]", $this->url(array_merge($params	, array('page' => 1))), $sURL); ?>'>Start</a> |
        <?php else: ?>
                <span class="disabled">Start</span> |
        <?php endif; ?>
    	
        <!-- Previous page link -->    
        <?php if ($iMaxRows > 0 && $iPage > 1): ?>
              <a href='<?=str_replace("[url]", $this->url(array_merge($params	, array('page' => $iPage-1))), $sURL); ?>'>&lt; Previous</a> |
        <?php else: ?>
            <span class="disabled">&lt; Previous</span> |
        <?php endif; ?>
        
        <!-- Numbered page links -->
        <?php for ($c = $iC; $c <= $iPageCount; $c++): ?>
            <?php if ($iPage != $c ): ?>
                <a href='<?=str_replace("[url]", $this->url(array_merge($params	, array('page' => $c))), $sURL); ?>'><?= $c; ?></a>
            <?php else: ?>
                <?= $c; ?>
            <?php endif; ?>
        <?php endfor; ?>
        
        <!-- Next page link -->
        <?php if ($iMaxRows > 0 && $iPage < $iPageCount): ?>
              | <a href='<?=str_replace("[url]", $this->url(array_merge($params	, array('page' => $iPage+1))), $sURL); ?>'>Next &gt;</a> |
        <?php else: ?>
            | <span class="disabled">Next &gt;</span> |
        <?php endif; ?>
        
        <!-- Last page link -->
        <?php if ($iMaxRows > 0 && $iPage != $iActualPageCount): ?>
              <a href='<?=str_replace("[url]", $this->url(array_merge($params	, array('page' => $iPageCount))), $sURL); ?>'>End</a>
        <?php else: ?>
            <span class="disabled">End</span>
        <?php endif; ?>
    
    </div>
         
    <div>Page <?= $iPage; ?> of <?= $iActualPageCount; ?></div>
    
    <p class="clear" />
    
 </div>
