<?php if(!class_exists('raintpl')){exit;}?><div class="maxed padded ajax-reload-container">
    <div class="row heading-row page-header">
        <div class="col-lg-8"><h1><?php echo $title;?></h1></div>
        <div class="col-lg-4 align-right">
            <?php if( $add_permission ){ ?>
                <a href="javascript://" data-open="<?php echo $add_url;?>" class="btn btn-primary open-overlay btn-sm big-overlay"><?php echo $add;?></a>
            <?php } ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
        <?php echo $table;?>
        </div>
    </div>
</div>