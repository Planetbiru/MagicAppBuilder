<?php

use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicAppTemplate\AppIncludeImpl;
use MagicAppTemplate\AppUserPermissionImpl;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "data-restoration", $appLanguage->getDataRestoration());
$userPermission = new AppUserPermissionImpl($appConfig, $database, $appUserRole, $currentModule, $currentUser);

$appInclude = new AppIncludeImpl($appConfig, $currentModule, __DIR__);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
$baseEntityNamespace = $appConfig->getApplication()->getBaseEntityTrashNamespace();
$directory = $baseDirectory . DIRECTORY_SEPARATOR . str_replace("\\", "/", $baseEntityNamespace);

$trashConfig = new SecretObject();
$trashConfigPath = __DIR__ . DIRECTORY_SEPARATOR . "inc.cfg" . DIRECTORY_SEPARATOR . "trash.yml";
if(file_exists($trashConfigPath))
{
    $trashConfig->loadYamlFile($trashConfigPath, false, true, true);
}

if($inputPost->getUserAction() === UserAction::RESTORE)
{
    $trashEntity = $inputPost->getTrashEntity();
    $primaryEntity = substr($trashEntity, 0, strlen($trashEntity) - 5);
    $file1 = $directory . DIRECTORY_SEPARATOR . $primaryEntity . ".php";
    $file2 = $directory . DIRECTORY_SEPARATOR . $trashEntity . ".php";

	// Check if file is exists
	if(file_exists($file1) && file_exists($file2))
    {
        require_once $file1;
        require_once $file2;

        $primaryEntityClass = $baseEntityNamespace . "\\" . $primaryEntity;    
        $trashEntityClass = $baseEntityNamespace . "\\" . $trashEntity;

		// Check if class is exists
		if(class_exists($primaryEntityClass) && class_exists($trashEntityClass) && $inputPost->countableCheckedRowId())
        {
            $dataToRestore = $inputPost->getCheckedRowId();
            foreach($dataToRestore as $value)
            {
                $trashEntityObject = new $trashEntityClass(null, $database);
                // Find the entity trash object by ID
                try
                {
                    $trashEntityObject->find($value);

					if(!$trashEntityObject->isRestored())
					{
						// Restore the entity trash object
						$primaryEntityObject = new $primaryEntityClass($trashEntityObject, $database);
						$primaryEntityObject->insert();

						// Update the entity trash object to mark it as restored
						$trashEntityObject->setRestored(true);
						$trashEntityObject->setAdminRestore($currentAction->getUserId());
						$trashEntityObject->setTimeRestore($currentAction->getTime());
						$trashEntityObject->setIpRestore($currentAction->getIp());
						$trashEntityObject->update();
					}
                }
                catch(Exception $e)
                {
                    // If the entity trash object is not found, skip to the next value
                    continue;
                }
            }
        }
    }
    $currentModule->redirectToItself();
    exit();
}
else if($inputPost->getUserAction() === UserAction::DELETE)
{
    $trashEntity = $inputPost->getTrashEntity();
    $primaryEntity = substr($trashEntity, 0, strlen($trashEntity) - 5);
    $file1 = $directory . DIRECTORY_SEPARATOR . $primaryEntity . ".php";
    $file2 = $directory . DIRECTORY_SEPARATOR . $trashEntity . ".php";

	// Check if file is exists
	if(file_exists($file1) && file_exists($file2))
    {
        require_once $file1;
        require_once $file2;

        $primaryEntityClass = $baseEntityNamespace . "\\" . $primaryEntity;    
        $trashEntityClass = $baseEntityNamespace . "\\" . $trashEntity;

		// Check if class is exists
		if(class_exists($primaryEntityClass) && class_exists($trashEntityClass) && $inputPost->countableCheckedRowId())
        {
            $dataToRestore = $inputPost->getCheckedRowId();
            foreach($dataToRestore as $value)
            {
                $trashEntityObject = new $trashEntityClass(null, $database);
                // Find the entity trash object by ID
                try
                {
                    $trashEntityObject->deleteRecordByPrimaryKey($value);
				}
				catch(Exception $e)
				{
					// Do nothing
				}
			}
		}
	}
	$currentModule->redirectToItself();
    exit();
}

require_once $appInclude->mainAppHeader(__DIR__);

if($inputGet->getTrashEntity() != "")
{
	$dataStatus = $inputGet->getDataStatus(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, false, true);
	?>
	<div class="page page-jambi page-list">
		<div class="jambi-wrapper">
		<div class="filter-section">
			<form action="" method="get" class="filter-form">
                <input type="hidden" name="user_action" value="<?php echo htmlspecialchars($inputGet->getUserAction());?>"/>
				<span class="filter-group">
					<span class="filter-label"><?php echo $appLanguage->getTrashEntity();?></span>
                    <span class="filter-control">
						<select name="trash_entity" class="form-control" id="trash_entity" onchange="this.form.submit();">
                            <option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
                            <?php
							if($trashConfig->issetTrashEntity())
							{
								$trashEntities = $trashConfig->getTrashEntity();
								foreach($trashEntities as $primaryEntity)
								{
									$selected = ($inputGet->getTrashEntity() == $primaryEntity->getName()) ? ' selected="selected"' : ''; // NOSONAR
									?>
									<option value="<?php echo $primaryEntity->getName();?>"<?php echo $selected;?>><?php echo $primaryEntity->getName();?></option>
									<?php
								}
							}
							?>
                        </select>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appLanguage->getTimeDeletion();?></span>
					<span class="filter-control">
						<input type="datetime-local" class="form-control" name="time_from" value="<?php echo $inputGet->getTimeFrom(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, false, true);?>" autocomplete="off"/>
					</span>

                    <span>
						<?php echo $appLanguage->getTo();?>
					</span>
                
                    <span class="filter-control">
						<input type="datetime-local" class="form-control" name="time_to" value="<?php echo $inputGet->getTimeTo(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, false, true);?>" autocomplete="off"/>
					</span>
				</span>

				<span class="filter-group">
					<span class="filter-label"><?php echo $appLanguage->getDataStatus();?></span>
                    <span class="filter-control">
						<select name="data_status" class="form-control" id="data_status" onchange="this.form.submit();">
                            <option value=""><?php echo $appLanguage->getDeleted();?></option>
                            <option value="restored"<?php echo $dataStatus == 'restored' ? ' selected="selected"' : '';?>><?php echo $appLanguage->getRestored();?></option>
							<option value="all"<?php echo $dataStatus == 'all' ? ' selected="selected"' : '';?>><?php echo $appLanguage->getAllStatus();?></option>
                        </select>
					</span>
				</span>
				
				<span class="filter-group">
					<button type="submit" class="btn btn-success" id="show_data"><?php echo $appLanguage->getButtonSearch();?></button>
				</span>

			</form>
		</div>
	<?php
    // Check if the entity exists
    $trashEntity = $inputGet->getTrashEntity();
    $file2 = $directory . DIRECTORY_SEPARATOR . $trashEntity . ".php";
	
    if(file_exists($file2))
    {
        require_once $file2;

        $trashEntityClass = $baseEntityNamespace . "\\" . $trashEntity;
        if(class_exists($trashEntityClass))
        {
            $dataLoader = new $trashEntityClass(null, $database);
            $tableInfo = $dataLoader->tableInfo();
			$primaryKeys = $tableInfo->getPrimaryKeys();
			if(isset($primaryKeys) && !empty($primaryKeys))
			{
				$primaryKey = array_values($primaryKeys)[0]['name'];
				$camelPrimaryKey = array_keys($primaryKeys)[0];
			}
			else
			{
				$primaryKey = '';
				$camelPrimaryKey = '';
			}
			
            $columns = $tableInfo->getSortedColumnName();

            // Find the entity trash object by ID
            try
            {
				
				$specification = PicoSpecification::getInstance();
				if($dataStatus == 'restored')
				{
					// Restored
					$specification->addAnd(
						PicoSpecification::getInstance()
							->addOr(PicoPredicate::getInstance()->equals(Field::of()->restored, true))
					);
				}
				else if($dataStatus == '')
				{
					// Deleted
					$specification->addAnd(
						PicoSpecification::getInstance()
							->addOr(PicoPredicate::getInstance()->equals(Field::of()->restored, null))
							->addOr(PicoPredicate::getInstance()->equals(Field::of()->restored, false))
					);
				}

				if($inputGet->getTimeFrom() != "")
				{
					$specification->addAnd(PicoPredicate::getInstance()
						->greaterThanOrEquals(Field::of()->timeDelete, $inputGet->getTimeFrom(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, false, true)));
				}
				if($inputGet->getTimeTo() != "")
				{
					$specification->addAnd(PicoPredicate::getInstance()
						->lessThanOrEquals(Field::of()->timeDelete, $inputGet->getTimeTo(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, false, true)));
				}
				$defaultSortable = PicoSortable::getInstance()
					->addSortable(new PicoSort(Field::of()->timeDelete, PicoSort::ORDER_TYPE_DESC));

				$sortable = PicoSortable::fromUserInput($inputGet, array_combine($columns, $columns), $defaultSortable);

				$pageable = new PicoPageable(new PicoPage($inputGet->getPage(), $dataControlConfig->getPageSize()), $sortable);
?>


	
		<div class="data-section">
			<?php try{
				$pageData = $dataLoader->findAll($specification, $pageable, $sortable, true, null, MagicObject::FIND_OPTION_NO_FETCH_DATA);
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
								<?php if($userPermission->isAllowedDelete() || $userPermission->isAllowedRestore()){ ?>
								<td class="data-controll data-selector" data-key="trash_id">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-trash-id"/>
								</td>
								<?php
								}
								?>
								<td class="data-controll data-number"><?php echo $appLanguage->getNumero();?></td>
								<td data-col-name="time_delete" class="order-controll"><a href="#"><?php echo $dataLoader->labelTimeDelete();?></a></td>
                                <?php
								
                                foreach($columns as $column)
                                {
									if($column != 'timeDelete')
									{
                                    ?>
                                    <td data-col-name="<?php echo $column;?>" class="order-controll"><a href="#"><?php echo $dataLoader->label($column);?></a></td>
                                    <?php
									}
                                }
                                ?>
							</tr>
						</thead>
					
						<tbody data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							while($trashData = $pageData->fetch())
							{
								$dataIndex++;
							?>
		
							<tr data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>">
								<?php if($userPermission->isAllowedDelete() || $userPermission->isAllowedRestore()){ ?>
								<td class="data-selector" data-key="trash_id">
									<input type="checkbox" class="checkbox check-slave checkbox-trash-id" name="checked_row_id[]" value="<?php echo $trashData->get($camelPrimaryKey);?>"/>
								</td>
								<?php
								}
								?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="time_delete"><?php echo $trashData->getTimeDelete();?></td>
								<?php
                                foreach($columns as $column)
                                {
									if($column != 'timeDelete')
									{
                                    ?>
                                    <td data-col-name="<?php echo $column;?>"><?php echo $trashData->get($column);?></td>
                                    <?php
									}
                                }
                                ?>
							</tr>
							<?php 
							}
							?>
		
						</tbody>
					</table>
				</div>
				<div class="button-wrapper">
					<div class="button-area">
						<input type="hidden" name="trash_entity" value="<?php echo $inputGet->getTrashEntity(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, false, true);?>">
						<?php if($userPermission->isAllowedRestore()){ ?>
						<button type="submit" class="btn btn-success" name="user_action" id="restore_selected" value="restore" data-confirmation="true"  data-onclik-title="<?php echo htmlspecialchars($appLanguage->getTitleRestoreConfirmation());?>" data-onclik-message="<?php echo htmlspecialchars($appLanguage->getWarningRestoreConfirmation());?>"><?php echo $appLanguage->getButtonRestore();?></button>
						<?php
						}
						?>
						<?php if($userPermission->isAllowedDelete()){ ?>
						<button type="submit" class="btn btn-danger" name="user_action" id="delete_selected" value="delete" data-confirmation="true"  data-onclik-title="<?php echo htmlspecialchars($appLanguage->getTitleDeletePermanentConfirmation());?>" data-onclik-message="<?php echo htmlspecialchars($appLanguage->getWarningDeletePermanentConfirmation());?>"><?php echo $appLanguage->getButtonDeletePermanent();?></button>
						<?php
						}
						?>
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
			</div>
	</div>

<?php 
                
            }
            catch(Exception $e)
            {
                // Do nothing
            }
        }
        
    }
    else
	{
		// If the entity trash class does not exist, show an error message
		?>            
		<div class="alert alert-danger"><?php echo $appLanguage->getMessageEntityTrashNotFound();?></div>
		<?php
	}
	?>
	</div>
	<?php
}
else
{
    ?>
    <div class="page page-jambi page-list">
	<div class="jambi-wrapper">
		<div class="filter-section">
			<form action="" method="get" class="filter-form">
				<span class="filter-group">
					<span class="filter-label"><?php echo $appLanguage->getTrashEntity();?></span>
                
                    <span class="filter-control">
						<select name="trash_entity" class="form-control" id="trash_entity" onchange="this.form.submit();">
                            <option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
                            <?php
                    if($trashConfig->issetTrashEntity())
                    {
                        $trashEntities = $trashConfig->getTrashEntity();
                        foreach($trashEntities as $trashEntity)
                        {
                            $selected = ($inputGet->getTrashEntity() == $trashEntity->getName()) ? ' selected="selected"' : '';
                            ?>
                            <option value="<?php echo $trashEntity->getName();?>"<?php echo $selected;?>><?php echo $trashEntity->getName();?></option>
                            <?php
                        }
                    }
                    ?>
                        </select>
					</span>
				</span>
				
				<span class="filter-group">
					<button type="submit" class="btn btn-success" id="show_data"><?php echo $appLanguage->getButtonSearch();?></button>
				</span>
                
			</form>
		</div>
    </div>
    <?php
}
require_once $appInclude->mainAppFooter(__DIR__);