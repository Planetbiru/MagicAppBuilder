<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use MagicObject\MagicObject;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicApp\AppEntityLanguage;
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicApp\AppUserPermission;
use MagicAdmin\AppIncludeImpl;
use MagicAdmin\Entity\Data\Notification;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "notification", $appLanguage->getNotification());
$userPermission = new AppUserPermission($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

$dataFilter = PicoSpecification::getInstance();
$dataFilter->addAnd(PicoPredicate::getInstance()->equals(Field::of()->receiverId, $currentAction->getUserId()));

if($inputPost->getUserAction() == UserAction::DELETE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS) as $rowId)
		{
			try
			{
				$specification = PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->notificationId, $rowId))
					->addAnd($dataFilter)
					;
				$notification = new Notification(null, $database);
				$notification->where($specification)
					->delete();
			}
			catch(Exception $e)
			{
				// Do something here to handle exception
				error_log($e->getMessage());
			}
		}
	}
	$currentModule->redirectToItself();
}
if($inputGet->getUserAction() == UserAction::CREATE)
{
$appEntityLanguage = new AppEntityLanguage(new Notification(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-insert">
	<div class="jambi-wrapper">
		<form name="createform" id="createform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getTitle();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="text" name="title" id="title"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getContent();?></td>
						<td>
							<textarea class="form-control" name="content" id="content" spellcheck="false"></textarea>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUrl();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="text" name="url" id="url"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsOpen();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="is_open" id="is_open" value="1"/> <?php echo $appEntityLanguage->getIsOpen();?></label>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeOpen();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="datetime-local" name="time_open" id="time_open"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsDelete();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="is_delete" id="is_delete" value="1"/> <?php echo $appEntityLanguage->getIsDelete();?></label>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<button type="submit" class="btn btn-success" name="user_action" value="create"><?php echo $appLanguage->getButtonSave();?></button>
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonCancel();?></button>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
<?php 
require_once $appInclude->mainAppFooter(__DIR__);
}
else if($inputGet->getUserAction() == UserAction::UPDATE)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->notificationId, $inputGet->getNotificationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$notification = new Notification(null, $database);
	try{
		$notification->findOne($specification);
		if($notification->issetNotificationId())
		{
$appEntityLanguage = new AppEntityLanguage(new Notification(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-update">
	<div class="jambi-wrapper">
		<form name="updateform" id="updateform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getTitle();?></td>
						<td>
							<input class="form-control" type="text" name="title" id="title" value="<?php echo $notification->getTitle();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getContent();?></td>
						<td>
							<textarea class="form-control" name="content" id="content" spellcheck="false"><?php echo $notification->getContent();?></textarea>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUrl();?></td>
						<td>
							<input class="form-control" type="text" name="url" id="url" value="<?php echo $notification->getUrl();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsOpen();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="is_open" id="is_open" value="1" <?php echo $notification->createCheckedIsOpen();?>/> <?php echo $appEntityLanguage->getIsOpen();?></label>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeOpen();?></td>
						<td>
							<input class="form-control" type="datetime-local" name="time_open" id="time_open" value="<?php echo $notification->getTimeOpen();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsDelete();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="is_delete" id="is_delete" value="1" <?php echo $notification->createCheckedIsDelete();?>/> <?php echo $appEntityLanguage->getIsDelete();?></label>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<button type="submit" class="btn btn-success" name="user_action" value="update"><?php echo $appLanguage->getButtonSave();?></button>
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonCancel();?></button>
							<input type="hidden" name="notification_id" value="<?php echo $notification->getNotificationId();?>"/>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
<?php 
		}
		else
		{
			// Do somtething here when data is not found
			?>
			<div class="alert alert-warning"><?php echo $appLanguage->getMessageDataNotFound();?></div>
			<?php 
		}
require_once $appInclude->mainAppFooter(__DIR__);
	}
	catch(Exception $e)
	{
require_once $appInclude->mainAppHeader(__DIR__);
		// Do somtething here when exception
		?>
		<div class="alert alert-danger"><?php echo $e->getMessage();?></div>
		<?php 
require_once $appInclude->mainAppFooter(__DIR__);
	}
}
else if($inputGet->getUserAction() == UserAction::DETAIL)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->notificationId, $inputGet->getNotificationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$notification = new Notification(null, $database);
	try{
		$subqueryMap = array(
		"receiverId" => array(
			"columnName" => "receiver_id",
			"entityName" => "AdminMin",
			"tableName" => "admin",
			"primaryKey" => "admin_id",
			"objectName" => "receiver",
			"propertyName" => "name"
		)
		);
		$notification->findOne($specification, null, $subqueryMap);
		if($notification->issetNotificationId())
		{
$appEntityLanguage = new AppEntityLanguage(new Notification(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
			// Define map here
			
?>
<div class="page page-jambi page-detail">
	<div class="jambi-wrapper">
		<?php
		if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($notification->getWaitingFor()))
		{
				?>
				<div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $notification->getWaitingFor());?></div>
				<?php
		}
		?>
		
		<form name="detailform" id="detailform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getTitle();?></td>
						<td><?php echo $notification->getTitle();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getContent();?></td>
						<td><?php echo $notification->getContent();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUrl();?></td>
						<td><?php echo $notification->getUrl();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getReceiver();?></td>
						<td><?php echo $notification->issetReceiver() ? $notification->getReceiver()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeCreate();?></td>
						<td><?php echo $notification->dateFormatTimeCreate($appConfig->getDateFormatDetail());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsOpen();?></td>
						<td><?php echo $notification->optionIsOpen($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeOpen();?></td>
						<td><?php echo $notification->getTimeOpen();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsDelete();?></td>
						<td><?php echo $notification->optionIsDelete($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<?php if($userPermission->isAllowedUpdate()){ ?>
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->notification_id, $notification->getNotificationId());?>';"><?php echo $appLanguage->getButtonUpdate();?></button>
							<?php } ?>
		
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonBackToList();?></button>
							<input type="hidden" name="notification_id" value="<?php echo $notification->getNotificationId();?>"/>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
<?php 
require_once $appInclude->mainAppFooter(__DIR__);
		}
		else
		{
			// Do somtething here when data is not found
			?>
			<div class="alert alert-warning"><?php echo $appLanguage->getMessageDataNotFound();?></div>
			<?php 
		}
	}
	catch(Exception $e)
	{
require_once $appInclude->mainAppHeader(__DIR__);
		// Do somtething here when exception
		?>
		<div class="alert alert-danger"><?php echo $e->getMessage();?></div>
		<?php 
require_once $appInclude->mainAppFooter(__DIR__);
	}
}
else 
{
$appEntityLanguage = new AppEntityLanguage(new Notification(), $appConfig, $currentUser->getLanguageId());

$specMap = array(
	
);
$sortOrderMap = array(
	"title" => "title",
	"content" => "content",
	"url" => "url",
	"receiverId" => "receiverId",
	"isOpen" => "isOpen",
	"timeOpen" => "timeOpen",
	"isDelete" => "isDelete"
);

// You can define your own specifications
// Pay attention to security issues
$specification = PicoSpecification::fromUserInput($inputGet, $specMap);
$specification->addAnd($dataFilter);


// You can define your own sortable
// Pay attention to security issues
$sortable = PicoSortable::fromUserInput($inputGet, $sortOrderMap, array(
	array(
		"sortBy" => "timeCreate", 
		"sortType" => PicoSort::ORDER_TYPE_DESC
	)
));

$pageable = new PicoPageable(new PicoPage($inputGet->getPage(), $dataControlConfig->getPageSize()), $sortable);
$dataLoader = new Notification(null, $database);

$subqueryMap = array(
"receiverId" => array(
	"columnName" => "receiver_id",
	"entityName" => "AdminMin",
	"tableName" => "admin",
	"primaryKey" => "admin_id",
	"objectName" => "receiver",
	"propertyName" => "name"
)
);

/*ajaxSupport*/
if(!$currentAction->isRequestViaAjax()){
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-list">
	<div class="jambi-wrapper">
		<div class="data-section" data-ajax-support="true" data-ajax-name="main-data">
			<?php } /*ajaxSupport*/ ?>
			<?php try{
				$pageData = $dataLoader->findAll($specification, $pageable, $sortable, true, $subqueryMap, MagicObject::FIND_OPTION_NO_FETCH_DATA);
				if($pageData->getTotalResult() > 0)
				{		
				    $pageControl = $pageData->getPageControl(Field::of()->page, $currentModule->getSelf())
				    ->setNavigation(
				        $dataControlConfig->getPrev(), $dataControlConfig->getNext(),
				        $dataControlConfig->getFirst(), $dataControlConfig->getLast()
				    )
				    ->setPageRange($dataControlConfig->getPageRange())
				    ;
			?>
			<div class="pagination pagination-top">
			    <div class="pagination-number">
			    <?php echo $pageControl; ?>
			    </div>
			</div>
			<form action="" method="post" class="data-form">
				<div class="data-wrapper">
					<table class="table table-row table-sort-by-column">
						<thead>
							<tr>
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-controll data-selector" data-key="notification_id">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-notification-id"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td class="data-controll data-editor">
									<span class="fa fa-edit"></span>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td class="data-controll data-viewer">
									<span class="fa fa-folder"></span>
								</td>
								<?php } ?>
								<td class="data-controll data-number"><?php echo $appLanguage->getNumero();?></td>
								<td data-col-name="title" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getTitle();?></a></td>
								<td data-col-name="content" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getContent();?></a></td>
								<td data-col-name="url" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getUrl();?></a></td>
								<td data-col-name="receiver_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getReceiver();?></a></td>
								<td data-col-name="is_open" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getIsOpen();?></a></td>
								<td data-col-name="time_open" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getTimeOpen();?></a></td>
								<td data-col-name="is_delete" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getIsDelete();?></a></td>
							</tr>
						</thead>
					
						<tbody data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							while($notification = $pageData->fetch())
							{
								$dataIndex++;
							?>
		
							<tr data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>">
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-selector" data-key="notification_id">
									<input type="checkbox" class="checkbox check-slave checkbox-notification-id" name="checked_row_id[]" value="<?php echo $notification->getNotificationId();?>"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td>
									<a class="edit-control" href="<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->notification_id, $notification->getNotificationId());?>"><span class="fa fa-edit"></span></a>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td>
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->notification_id, $notification->getNotificationId());?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="title"><?php echo $notification->getTitle();?></td>
								<td data-col-name="content"><?php echo $notification->getContent();?></td>
								<td data-col-name="url"><?php echo $notification->getUrl();?></td>
								<td data-col-name="receiver_id"><?php echo $notification->issetReceiver() ? $notification->getReceiver()->getName() : "";?></td>
								<td data-col-name="is_open"><?php echo $notification->optionIsOpen($appLanguage->getYes(), $appLanguage->getNo());?></td>
								<td data-col-name="time_open"><?php echo $notification->getTimeOpen();?></td>
								<td data-col-name="is_delete"><?php echo $notification->optionIsDelete($appLanguage->getYes(), $appLanguage->getNo());?></td>
							</tr>
							<?php 
							}
							?>
		
						</tbody>
					</table>
				</div>
				<div class="button-wrapper">
					<div class="form-control-container button-area">
						<?php if($userPermission->isAllowedDelete()){ ?>
						<button type="submit" class="btn btn-danger" name="user_action" value="delete" data-onclik-message="<?php echo htmlspecialchars($appLanguage->getWarningDeleteConfirmation());?>"><?php echo $appLanguage->getButtonDelete();?></button>
						<?php } ?>
					</div>
				</div>
			</form>
			<div class="pagination pagination-bottom">
			    <div class="pagination-number">
			    <?php echo $pageControl; ?>
			    </div>
			</div>
			
			<?php 
			}
			else
			{
			    ?>
			    <div class="alert alert-info"><?php echo $appLanguage->getMessageDataNotFound();?></div>
			    <?php
			}
			?>
			
			<?php
			}
			catch(Exception $e)
			{
			    ?>
			    <div class="alert alert-danger"><?php echo $appInclude->printException($e);?></div>
			    <?php
			} 
			?>
			<?php /*ajaxSupport*/ if(!$currentAction->isRequestViaAjax()){ ?>
		</div>
	</div>
</div>
<?php 
require_once $appInclude->mainAppFooter(__DIR__);
}
/*ajaxSupport*/
}

