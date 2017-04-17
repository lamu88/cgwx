<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-gw', TEMPLATE_INCLUDEPATH)) : (include template('common/header-gw', TEMPLATE_INCLUDEPATH));?>
<ol class="breadcrumb">
	<li><a href="./?refresh"><i class="fa fa-home"></i></a></li>
	<li><a href="<?php  echo url('system/welcome');?>">系统</a></li>
	<li class="active">相关设置</li>
</ol>
<ul class="nav nav-tabs">
     
    <li <?php  if($do == 'payset') { ?>class="active"<?php  } ?>><a href="<?php  echo url('shop/mpayset/payset');?>">支付设置</a></li>
    <li <?php  if($do == 'mkset') { ?>class="active"<?php  } ?>><a href="<?php  echo url('shop/mkdel/mkset');?>">相关设置</a></li>   
</ul>
<?php  if($_W['isfounder']) { ?>
<form class="form-horizontal form" id="form1" action="" method="post" enctype="multipart/form-data">
		<input type="hidden" name="sid" value="<?php  echo $sid;?>">
		<div class="main">
			<div class="panel panel-default">
				<div class="panel-heading">模块相关设置</div>
				<div class="panel-body">
                    <div class="form-group">
						<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span class="require">* </span>模块检测</label>
						<div class="col-sm-9 col-xs-12">
                        <?php  if($starsit==0) { ?>
							<a href="<?php  echo url('shop/mkdel/go')?>" class="btn btn-success"><i class="fa fa-circle"></i> 开启</a>
                            
                            <a href="<?php  echo $_W['siteroot'];?>web/source/member/common/startjk.php" class="btn btn-success"><i class="fa fa-circle"></i> 开启自动检测</a>
                            
                        <?php  } else if($starsit==1) { ?>
                            <a href="<?php  echo url('shop/mkdel/go')?>" class="btn btn-danger"><i class="fa fa-circle"></i> 已开启</a>
                        <?php  } ?>
                            <span id="helpBlock" class="help-block">开启之后每24小时检测一次模块到期时间，到期后将自动停止该模块的使用。（重启web服务器后请重新开启改设置）</span>

						</div>
					</div>		
				</div>
			</div>		
			
		</div>
	</form>
<?php  } ?>


<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>