<?

$iPage 				= isset($vars["page"]) ? $vars["page"] : 1;
$iRowPerPage 	= isset($vars["rowsperpage"]) ? $vars["rowsperpage"] : 10;
$iMaxRows			= isset($vars["max"]) ? $vars["max"] : 0;

$iPageCount		= ceil($iMaxRows / $iRowPerPage);

$jsFunc       = $vars["js"];
//var_dump($sURL);
?>

<div class="pagination" style="width:100%; padding-top: 15px">
    
    <div>
      
        <!-- First page link -->
        <?php if ($iMaxRows > 0 && $iPage > 1): ?>
              <a href='javascript:<?=$jsFunc?>(1)'>Start</a> |
        <?php else: ?>
                <span class="disabled">Start</span> |
        <?php endif; ?>
    	
        <!-- Previous page link -->    
        <?php if ($iMaxRows > 0 && $iPage > 1): ?>
              <a href='javascript:<?=$jsFunc?>(<?=$iPage-1?>)'>&lt; Previous</a> |
        <?php else: ?>
            <span class="disabled">&lt; Previous</span> |
        <?php endif; ?>
        
        <!-- Numbered page links -->
        <?php for ($c = 1; $c <= $iPageCount; $c++): ?>
            <?php if ($iPage != $c ): ?>
                <a href='javascript:<?=$jsFunc?>(<?=$c?>)'><?= $c; ?></a>
            <?php else: ?>
                <?= $c; ?>
            <?php endif; ?>
        <?php endfor; ?>
        
        <!-- Next page link -->
        <?php if ($iMaxRows > 0 && $iPage < $iPageCount): ?>
              | <a href='javascript:<?=$jsFunc?>(<?=$iPage+1?>)'>Next &gt;</a> |
        <?php else: ?>
            | <span class="disabled">Next &gt;</span> |
        <?php endif; ?>
        
        <!-- Last page link -->
        <?php if ($iMaxRows > 0 && $iPage != $iPageCount): ?>
              <a href='javascript:<?=$jsFunc?>(<?=$iPageCount?>)'>End</a>
        <?php else: ?>
            <span class="disabled">End</span>
        <?php endif; ?>
    
    </div>
         
    <div>Page <?= $iPage; ?> of <?= $iPageCount; ?></div>
    
    <p class="clear" />
    
 </div>
