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
use MagicApp\AppFormBuilder;
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicAdmin\AppIncludeImpl;
use MagicAdmin\AppUserPermissionExtended;
use MagicAdmin\Entity\Data\Message;
use MagicAdmin\Entity\Data\AdminMin;
use MagicAdmin\Entity\Data\MessageFolderMin;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "message", $appLanguage->getMessage());
$userPermission = new AppUserPermissionExtended($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

$dataFilter = null;

if($inputPost->getUserAction() == UserAction::CREATE)
{
	$message = new Message(null, $database);
	$message->setSubject($inputPost->getSubject(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$message->setContent($inputPost->getContent(PicoFilterConstant::FILTER_DEFAULT, false, false, true));
	$message->setSenderId($entityAdmin->getAdminId());
	$message->setReceiverId($inputPost->getReceiverId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$message->setAdminCreate($currentAction->getUserId());
	$message->setTimeCreate($currentAction->getTime());
	$message->setIpCreate($currentAction->getIp());
	$message->setAdminEdit($currentAction->getUserId());
	$message->setTimeEdit($currentAction->getTime());
	$message->setIpEdit($currentAction->getIp());
	try
	{
		$message->insert();
		$newId = $message->getMessageId();
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->message_id, $newId);
	}
	catch(Exception $e)
	{
		$currentModule->redirectToItself();
	}
}
else if($inputPost->getUserAction() == UserAction::ACTIVATE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS) as $rowId)
		{
			$message = new Message(null, $database);
			try
			{
				$message->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->messageId, $rowId))
					->addAnd(PicoPredicate::getInstance()->notEquals(Field::of()->active, true))
					->addAnd($dataFilter)
				)
				->setAdminEdit($currentAction->getUserId())
				->setTimeEdit($currentAction->getTime())
				->setIpEdit($currentAction->getIp())
				->setActive(true)
				->update();
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
else if($inputPost->getUserAction() == UserAction::DEACTIVATE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS) as $rowId)
		{
			$message = new Message(null, $database);
			try
			{
				$message->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->messageId, $rowId))
					->addAnd(PicoPredicate::getInstance()->notEquals(Field::of()->active, false))
					->addAnd($dataFilter)
				)
				->setAdminEdit($currentAction->getUserId())
				->setTimeEdit($currentAction->getTime())
				->setIpEdit($currentAction->getIp())
				->setActive(false)
				->update();
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
else if($inputPost->getUserAction() == UserAction::DELETE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS) as $rowId)
		{
			try
			{
				$specification = PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->messageId, $rowId))
					->addAnd($dataFilter)
					;
				$message = new Message(null, $database);
				$message->where($specification)
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
$appEntityLanguage = new AppEntityLanguage(new Message(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
?>
<link rel="stylesheet" href="../lib.assets/summernote/0.8.20/summernote.css">
<link rel="stylesheet" href="../lib.assets/summernote/0.8.20/summernote-bs4.min.css">
<script type="text/javascript" src="../lib.assets/popper/popper.min.js"></script>
<script type="text/javascript" src="../lib.assets/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../lib.assets/summernote/0.8.20/summernote.js"></script>
<script type="text/javascript" src="../lib.assets/summernote/0.8.20/summernote-bs4.min.js"></script>
<style>
	.note-hint-popover {
		position: absolute;
	}
</style>
<script>
	var elements = [];
	jQuery(function($) {
		let editors = [];
		var activeEditor = null;	
		$('textarea').each(function(index){
			$(this).attr('data-index', index);
			$(this).addClass('summernote-source');
			editors[index] = $(this).summernote({
				spellcheck: false,
				height: 200,
				hint: {
					words: [],
					match: /\b(\w{1,})$/,
					search: function (keyword, callback) {
						callback($.grep(this.words, function (item) {
							return item.indexOf(keyword) === 0;
						}));
					}
				},
				toolbar: [
					['style', ['style', 'bold', 'italic', 'underline']],
					['para', ['ul', 'ol', 'paragraph']],
					['font', ['fontname', 'fontsize', 'color', 'background']],
					['insert', ['picture', 'table']],
				],
				callbacks: {
					onImageUpload: function (files) {
					},
					onMediaDelete: function (target) {
					},
					onFocus: function() {
						let idx = $(this).attr('data-index');
						activeEditor = editors[idx];
						$('.note-editable').attr('spellcheck', 'false');
					}
				}
			});
			elements[index] = $(this);
		});

		$('textarea.summernote-source').each(function(index) {
			$(this).next().closest('.note-editor').on('click', function(e) {
				activeEditor = editors[index];  
				if (activeEditor) {
					activeEditor.summernote('focus');
				}
			});
		});

		$(document).on('change', '.note-image-input.form-control-file.note-form-control.note-input', function(e) {
			var files = e.target.files;

			if (files.length > 0) {
				var file = files[0];
				if (file.type.startsWith('image/')) {
					let mdl = $(this).closest('.modal-dialog');
					let btn = mdl.find('.note-image-btn');
					btn[0].disabled = false;
				} else {
					alert("Please select an image file.");
				}
			}
		});

		$(document).on('click', '.note-image-btn', function() {
			let btn = $(this);
			if (activeEditor) {
				var fileInput = $(this).closest('.note-modal').find('.note-image-input.form-control-file.note-form-control.note-input')[0];
				var file = fileInput.files[0];
				if (file) {
					var reader = new FileReader();
					reader.onload = function(event) {
						var base64Image = event.target.result;
						activeEditor.summernote('insertImage', base64Image);
						fileInput.value = "";
						btn.closest('.modal').modal('hide');  // Close the modal
					};
					reader.readAsDataURL(file);
				}
			} else {
				console.log('No active editor found.');
			}
		});
	});


</script>
<div class="page page-jambi page-insert">
	<div class="jambi-wrapper">
		<form name="createform" id="createform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getReceiver();?></td>
						<td>
							<select class="form-control" name="receiver_id" id="receiver_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false))
									->addAnd(PicoPredicate::getInstance()->notEquals(Field::of()->adminId, $entityAdmin->getAdminId())), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminId, Field::of()->name)
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSubject();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="text" name="subject" id="subject"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getContent();?></td>
						<td>
							<textarea class="form-control" name="content" id="content" spellcheck="false"></textarea>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->messageId, $inputGet->getMessageId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$message = new Message(null, $database);
	try{
		$message->findOne($specification);
		if($message->issetMessageId())
		{
$appEntityLanguage = new AppEntityLanguage(new Message(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
?>
<link rel="stylesheet" href="../lib.assets/summernote/0.8.20/summernote.css">
<link rel="stylesheet" href="../lib.assets/summernote/0.8.20/summernote-bs4.min.css">
<script type="text/javascript" src="../lib.assets/popper/popper.min.js"></script>
<script type="text/javascript" src="../lib.assets/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../lib.assets/summernote/0.8.20/summernote.js"></script>
<script type="text/javascript" src="../lib.assets/summernote/0.8.20/summernote-bs4.min.js"></script>
<style>
	.note-hint-popover {
		position: absolute;
	}
</style>
<script>
	var elements = [];
	jQuery(function($) {
		let editors = [];
		var activeEditor = null;	
		$('textarea').each(function(index){
			$(this).attr('data-index', index);
			$(this).addClass('summernote-source');
			editors[index] = $(this).summernote({
				spellcheck: false,
				height: 200,
				hint: {
					words: [],
					match: /\b(\w{1,})$/,
					search: function (keyword, callback) {
						callback($.grep(this.words, function (item) {
							return item.indexOf(keyword) === 0;
						}));
					}
				},
				toolbar: [
					['style', ['style', 'bold', 'italic', 'underline']],
					['para', ['ul', 'ol', 'paragraph']],
					['font', ['fontname', 'fontsize', 'color', 'background']],
					['insert', ['picture', 'table']],
				],
				callbacks: {
					onImageUpload: function (files) {
					},
					onMediaDelete: function (target) {
					},
					onFocus: function() {
						let idx = $(this).attr('data-index');
						activeEditor = editors[idx];
						$('.note-editable').attr('spellcheck', 'false');
					}
				}
			});
			elements[index] = $(this);
		});

		$('textarea.summernote-source').each(function(index) {
			$(this).next().closest('.note-editor').on('click', function(e) {
				activeEditor = editors[index];  
				if (activeEditor) {
					activeEditor.summernote('focus');
				}
			});
		});

		$(document).on('change', '.note-image-input.form-control-file.note-form-control.note-input', function(e) {
			var files = e.target.files;

			if (files.length > 0) {
				var file = files[0];
				if (file.type.startsWith('image/')) {
					let mdl = $(this).closest('.modal-dialog');
					let btn = mdl.find('.note-image-btn');
					btn[0].disabled = false;
				} else {
					alert("Please select an image file.");
				}
			}
		});

		$(document).on('click', '.note-image-btn', function() {
			let btn = $(this);
			if (activeEditor) {
				var fileInput = $(this).closest('.note-modal').find('.note-image-input.form-control-file.note-form-control.note-input')[0];
				var file = fileInput.files[0];
				if (file) {
					var reader = new FileReader();
					reader.onload = function(event) {
						var base64Image = event.target.result;
						activeEditor.summernote('insertImage', base64Image);
						fileInput.value = "";
						btn.closest('.modal').modal('hide');  // Close the modal
					};
					reader.readAsDataURL(file);
				}
			} else {
				console.log('No active editor found.');
			}
		});
	});


</script>
<div class="page page-jambi page-update">
	<div class="jambi-wrapper">
		<form name="updateform" id="updateform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getSubject();?></td>
						<td>
							<input class="form-control" type="text" name="subject" id="subject" value="<?php echo $message->getSubject();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getContent();?></td>
						<td>
							<textarea class="form-control" name="content" id="content" spellcheck="false">
&lt;p&gt;&nbsp;&lt;/p&gt;
&lt;p&gt;Original message&lt;/p&gt;
&lt;blockquote&gt;<?php echo $message->getContent();?>&lt;/blockquote&gt;
</textarea>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSender();?></td>
						<td>
							<select class="form-control" name="sender_id" id="sender_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminId, Field::of()->name, $message->getSenderId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getReceiver();?></td>
						<td>
							<select class="form-control" name="receiver_id" id="receiver_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminId, Field::of()->name, $message->getReceiverId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getMessageFolder();?></td>
						<td>
							<select class="form-control" name="message_folder_id" id="message_folder_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new MessageFolderMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->messageFolderId, Field::of()->name, $message->getMessageFolderId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsCopy();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="is_copy" id="is_copy" value="1" <?php echo $message->createCheckedIsCopy();?>/> <?php echo $appEntityLanguage->getIsCopy();?></label>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsOpen();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="is_open" id="is_open" value="1" <?php echo $message->createCheckedIsOpen();?>/> <?php echo $appEntityLanguage->getIsOpen();?></label>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeOpen();?></td>
						<td>
							<input class="form-control" type="datetime-local" name="time_open" id="time_open" value="<?php echo $message->getTimeOpen();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsDelete();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="is_delete" id="is_delete" value="1" <?php echo $message->createCheckedIsDelete();?>/> <?php echo $appEntityLanguage->getIsDelete();?></label>
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
							<input type="hidden" name="message_id" value="<?php echo $message->getMessageId();?>"/>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->messageId, $inputGet->getMessageId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$message = new Message(null, $database);
	try{
		$subqueryMap = array(
		"senderId" => array(
			"columnName" => "sender_id",
			"entityName" => "AdminMin",
			"tableName" => "admin",
			"primaryKey" => "admin_id",
			"objectName" => "sender",
			"propertyName" => "name"
		), 
		"receiverId" => array(
			"columnName" => "receiver_id",
			"entityName" => "AdminMin",
			"tableName" => "admin",
			"primaryKey" => "admin_id",
			"objectName" => "receiver",
			"propertyName" => "name"
		), 
		"messageFolderId" => array(
			"columnName" => "message_folder_id",
			"entityName" => "MessageFolderMin",
			"tableName" => "message_folder",
			"primaryKey" => "message_folder_id",
			"objectName" => "message_folder",
			"propertyName" => "name"
		)
		);
		$message->findOne($specification, null, $subqueryMap);
		if($message->issetMessageId())
		{
$appEntityLanguage = new AppEntityLanguage(new Message(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
			// Define map here
			
?>
<div class="page page-jambi page-detail">
	<div class="jambi-wrapper">
		<?php
		if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($message->getWaitingFor()))
		{
				?>
				<div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $message->getWaitingFor());?></div>
				<?php
		}
		?>
		
		<form name="detailform" id="detailform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getSubject();?></td>
						<td><?php echo $message->getSubject();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getContent();?></td>
						<td><?php echo $message->getContent();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSender();?></td>
						<td><?php echo $message->issetSender() ? $message->getSender()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getReceiver();?></td>
						<td><?php echo $message->issetReceiver() ? $message->getReceiver()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getMessageFolder();?></td>
						<td><?php echo $message->issetMessageFolder() ? $message->getMessageFolder()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsCopy();?></td>
						<td><?php echo $message->optionIsCopy($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeCreate();?></td>
						<td><?php echo $message->dateFormatTimeCreate($appConfig->getDateFormatDetail());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsOpen();?></td>
						<td><?php echo $message->optionIsOpen($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeOpen();?></td>
						<td><?php echo $message->getTimeOpen();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIsDelete();?></td>
						<td><?php echo $message->optionIsDelete($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl('reply', Field::of()->message_id, $message->getMessageId());?>';"><?php echo $appLanguage->getButtonReply();?></button>		
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonBackToList();?></button>
							<input type="hidden" name="message_id" value="<?php echo $message->getMessageId();?>"/>
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
$appEntityLanguage = new AppEntityLanguage(new Message(), $appConfig, $currentUser->getLanguageId());

$specMap = array(
	"subject" => PicoSpecification::filter("subject", "fulltext"),
	"senderId" => PicoSpecification::filter("senderId", "fulltext"),
	"receiverId" => PicoSpecification::filter("receiverId", "fulltext"),
	"messageFolderId" => PicoSpecification::filter("messageFolderId", "fulltext")
);
$sortOrderMap = array(
	"subject" => "subject",
	"content" => "content",
	"senderId" => "senderId",
	"receiverId" => "receiverId",
	"messageFolderId" => "messageFolderId",
	"isCopy" => "isCopy",
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
$dataLoader = new Message(null, $database);

$subqueryMap = array(
"senderId" => array(
	"columnName" => "sender_id",
	"entityName" => "AdminMin",
	"tableName" => "admin",
	"primaryKey" => "admin_id",
	"objectName" => "sender",
	"propertyName" => "name"
), 
"receiverId" => array(
	"columnName" => "receiver_id",
	"entityName" => "AdminMin",
	"tableName" => "admin",
	"primaryKey" => "admin_id",
	"objectName" => "receiver",
	"propertyName" => "name"
), 
"messageFolderId" => array(
	"columnName" => "message_folder_id",
	"entityName" => "MessageFolderMin",
	"tableName" => "message_folder",
	"primaryKey" => "message_folder_id",
	"objectName" => "message_folder",
	"propertyName" => "name"
)
);

/*ajaxSupport*/
if(!$currentAction->isRequestViaAjax()){
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-list">
	<div class="jambi-wrapper">
		<div class="filter-section">
			<form action="" method="get" class="filter-form">
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getSubject();?></span>
					<span class="filter-control">
						<input type="text" name="subject" class="form-control" value="<?php echo $inputGet->getSubject();?>" autocomplete="off"/>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getSender();?></span>
					<span class="filter-control">
							<select class="form-control" name="sender_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminId, Field::of()->name, $inputGet->getSenderId())
								; ?>
							</select>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getReceiver();?></span>
					<span class="filter-control">
							<select class="form-control" name="receiver_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminId, Field::of()->name, $inputGet->getReceiverId())
								; ?>
							</select>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getMessageFolder();?></span>
					<span class="filter-control">
							<select class="form-control" name="message_folder_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new MessageFolderMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->messageFolderId, Field::of()->name, $inputGet->getMessageFolderId())
								; ?>
							</select>
					</span>
				</span>
				
				<span class="filter-group">
					<button type="submit" class="btn btn-success"><?php echo $appLanguage->getButtonSearch();?></button>
				</span>
				<?php if($userPermission->isAllowedCreate()){ ?>
		
				<span class="filter-group">
					<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::CREATE);?>'"><?php echo $appLanguage->getButtonAdd();?></button>
				</span>
				<?php } ?>
			</form>
		</div>
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
								<td class="data-controll data-selector" data-key="message_id">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-message-id"/>
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
								<td data-col-name="subject" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getSubject();?></a></td>
								<td data-col-name="content" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getContent();?></a></td>
								<td data-col-name="sender_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getSender();?></a></td>
								<td data-col-name="receiver_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getReceiver();?></a></td>
								<td data-col-name="message_folder_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getMessageFolder();?></a></td>
								<td data-col-name="is_copy" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getIsCopy();?></a></td>
								<td data-col-name="is_open" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getIsOpen();?></a></td>
								<td data-col-name="time_open" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getTimeOpen();?></a></td>
								<td data-col-name="is_delete" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getIsDelete();?></a></td>
							</tr>
						</thead>
					
						<tbody data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							while($message = $pageData->fetch())
							{
								$dataIndex++;
							?>
		
							<tr data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>">
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-selector" data-key="message_id">
									<input type="checkbox" class="checkbox check-slave checkbox-message-id" name="checked_row_id[]" value="<?php echo $message->getMessageId();?>"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td>
									<a class="edit-control" href="<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->message_id, $message->getMessageId());?>"><span class="fa fa-edit"></span></a>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td>
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->message_id, $message->getMessageId());?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="subject"><?php echo $message->getSubject();?></td>
								<td data-col-name="content"><?php echo $message->getContent();?></td>
								<td data-col-name="sender_id"><?php echo $message->issetSender() ? $message->getSender()->getName() : "";?></td>
								<td data-col-name="receiver_id"><?php echo $message->issetReceiver() ? $message->getReceiver()->getName() : "";?></td>
								<td data-col-name="message_folder_id"><?php echo $message->issetMessageFolder() ? $message->getMessageFolder()->getName() : "";?></td>
								<td data-col-name="is_copy"><?php echo $message->optionIsCopy($appLanguage->getYes(), $appLanguage->getNo());?></td>
								<td data-col-name="is_open"><?php echo $message->optionIsOpen($appLanguage->getYes(), $appLanguage->getNo());?></td>
								<td data-col-name="time_open"><?php echo $message->getTimeOpen();?></td>
								<td data-col-name="is_delete"><?php echo $message->optionIsDelete($appLanguage->getYes(), $appLanguage->getNo());?></td>
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

