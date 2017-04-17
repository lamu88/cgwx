<?php defined('IN_IA') or exit('Access Denied');?><div class="page-header">
	<h4><i class="fa fa-comments"></i> APP应用列表</h4>
</div>
<div class="panel panel-default row">
	<div class='alert alert-warning' style='font-size:14px'>
        	温馨提示：1.APP云打包功能，可以将一个手机网站打包成一个可以直接安装到手机里的应用软件。<br/>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			2.由于安装后的应用软件运行于浏览器环境，使用APP云打包功能暂不支持类似需要使用微信授权功能的网站和需要使用微信支付功能的网站。

    </div>
	<div class="panel panel-info">
		<div class="panel-heading">筛选</div>
		<div class="panel-body">
			<form action="" method="get" class="form-horizontal" role="form">
				<input type="hidden" name="c" value="agent">
				<input type="hidden" name="a" value="agentusers">
				<input type="hidden" name="do" value="list">
				<input type="hidden" name="createtime" value="<?php  echo $_GPC['createtime'];?>">
				<div class="form-group">
					<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label">添加时间</label>
					<div class="col-sm-8 col-lg-9 col-xs-12">
						<div class="btn-group">
							<a href="<?php  echo filter_url('createtime:0');?>" class="btn <?php  if($_GPC['createtime'] == 0) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">不限</a>
							<a href="<?php  echo filter_url('createtime:3');?>" class="btn <?php  if($_GPC['createtime'] == 3) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">三天内</a>
							<a href="<?php  echo filter_url('createtime:7');?>" class="btn <?php  if($_GPC['createtime'] == 7) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">一周内</a>
							<a href="<?php  echo filter_url('createtime:30');?>" class="btn <?php  if($_GPC['createtime'] == 30) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">一月内</a>
							<a href="<?php  echo filter_url('createtime:90');?>" class="btn <?php  if($_GPC['createtime'] == 90) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">三月内</a>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label">标题</label>
					<div class="col-sm-8 col-lg-3 col-xs-12">
						<input class="form-control" name="title" id="" type="text" value="<?php  echo $_GPC['title'];?>">
					</div>
					<div class="pull-left col-xs-12 col-sm-2 col-lg-2">
						<button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
					</div>
				</div>
			</form>
		</div>
	</div>
		<div class="panel panel-default">
			<div class="panel-body table-responsive">
				<table class="table table-hover">
					<thead>
						<tr>
							<th width="80">APPID</th>
							<th width="100">LOGO</th>
							<th width="180">名称</th>
							<th width="180">网址</th>
							<th width="100">平台</th>
							<th width="130">系统标题栏</th>
							<th>横竖屏</th>
							<th>创建时间</th>
							<th class="text-right">操作</th>
						</tr>
					</thead>
					<tbody>
					<?php  if(is_array($applist)) { foreach($applist as $app) { ?>
						<tr>
							<td><?php  echo $app['web_app_id'];?></td>
							<td><?php  echo $app['icopic'];?></td>
							<td><?php  echo $app['name'];?></td>
							<td><?php  echo $app['weburl'];?></td>
							<td><?php  if($app['apptype']){echo 'IOS';}else{echo '安卓';}?></td>
							<td><?php  if($app['hidetop']){echo '隐藏';}else{echo '显示';}?></td>
							<td><?php  if($app['screen']){echo '横屏';}else{echo '竖屏';}?></td>
							<td><?php  echo date('Y-m-d H:i', $user['createtime']);?></td>
							<td class="text-right">
								<a href="<?php  echo url('fournet/app/download', array('id' => $app['web_app_id']));?>" class="btn btn-default">下载</a>
							</td>
						</tr>
					<?php  } } ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php  echo $pager;?>
	</form>
</div>
</div>

