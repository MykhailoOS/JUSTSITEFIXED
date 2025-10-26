<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/language.php';
start_session_if_needed();

// Initialize language system
LanguageManager::init();
header('Content-Type: text/html; charset=utf-8');

// Determine current user
$uid = current_user_id();

// If not logged in — redirect to landing page
if (!$uid) {
    header('Location: landing.php');
    exit;
}

// User is logged in: render the builder UI directly (no index.html dependency)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\') . '/';

// fetch user name
$displayName = '';
try {
    $pdo = DatabaseConnectionProvider::getConnection();
    $stmt = $pdo->prepare('SELECT COALESCE(NULLIF(name, ""), email) AS dn FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    $row = $stmt->fetch();
    if ($row && !empty($row['dn'])) { 
        $displayName = htmlspecialchars($row['dn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); 
    }
} catch (Throwable $e) { 
    /* ignore */ 
}
?>
<?php include __DIR__ . '/components/loading-screen.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<base href="<?php echo $basePath; ?>">
	<title><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> — <?php echo LanguageManager::t('header_builder'); ?></title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100..900&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link rel="stylesheet" href="styles/main.css?v=<?php echo time(); ?>">
	<script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
	<div class="header">
		<div class="bone">
			<div class="header_group">
				<div class="header_logo"><img src="images/icons/logo.svg" alt="logo"></div>
				<div style="display:flex;align-items:center;gap:8px;">
					<?php if ($displayName !== ''): ?>
						<div class="muted" style="color:#424242;font-size:14px;white-space:nowrap;max-width:200px;overflow:hidden;text-overflow:ellipsis;"><?php echo $displayName; ?></div>
					<?php endif; ?>
					
					<a href="profile.php" class="header_profile" title="<?php echo LanguageManager::t('header_profile'); ?>"></a>
				</div>
			</div>
		</div>
	</div>
	<div class="general">
		<div class="general_sidebar_left">
			<div class="general_sidebar_left_block">
				<div class="general_sidebar_title_wrapper">
					<div class="general_sidebar_title"><?php echo LanguageManager::t('nav_dashboard'); ?></div>
				</div>
				<div class="general_sidebar_nav">
					<a href="profile.php" class="general_sidebar_nav_li" id="homeBtn" title="<?php echo LanguageManager::t('nav_home'); ?>">
						<div class="general_sidebar_nav_li_icon">
							<img src="images/icons/home.svg">
						</div>
						<div class="general_sidebar_nav_li_title"><?php echo LanguageManager::t('nav_home'); ?></div>
					</a>
				</div>
			</div>
			<div class="general_sidebar_left_logout">
				<a href="logout.php" class="general_sidebar_nav_li" id="logoutBtn" title="<?php echo LanguageManager::t('nav_logout'); ?>">
					<div class="general_sidebar_nav_li_icon">
						<img src="images/icons/logout.svg">
					</div>
					<div class="general_sidebar_nav_li_title"><?php echo LanguageManager::t('nav_logout'); ?></div>
				</a>
			</div>
		</div>
		<div class="general_group">
			<div class="general_menu">
				<div class="general_views">
					<div class="general_view general_view_desktop active">
						<div class="general_view_icon">
							<img src="images/icons/desktop.svg">
						</div>
						<div class="general_view_title">Desktop</div>
					</div>
					<div class="general_view general_view_mobile">
						<div class="general_view_icon">
							<img src="images/icons/desktop.svg">
						</div>
						<div class="general_view_title">Mobile</div>
					</div>
				</div>
				<div class="general_add_buttons">
					<a href="javascript:void(0)" class="general_add_button" id="addElementBtn" title="<?php echo LanguageManager::t('btn_add_element'); ?>" role="button">
						<img src="images/icons/plus.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="newProjectBtn" title="<?php echo LanguageManager::t('btn_new_project'); ?>" role="button">
						<img src="images/icons/home.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="loadBtn" title="<?php echo LanguageManager::t('btn_load'); ?>" role="button">
						<img src="images/icons/click.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="editBtn" title="<?php echo LanguageManager::t('btn_edit'); ?>" role="button" style="display: none;">
						<img src="images/icons/text.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="saveBtn" title="<?php echo LanguageManager::t('btn_save'); ?>" role="button">
						<img src="images/icons/save.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="deployBtn" title="<?php echo LanguageManager::t('btn_deploy'); ?>" role="button">
						<img src="images/icons/share.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="exportBtn" title="<?php echo LanguageManager::t('btn_export'); ?>" role="button">
						<img src="images/icons/share.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button ai-helper-btn" id="aiHelperBtn" title="<?php echo LanguageManager::t('btn_ai_helper'); ?>" role="button">
						<img src="images/icons/stars.svg" class="ai-helper-icon">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="saveToLibraryBtn" title="Share to Community" role="button">
						<span class="material-icons">share</span>
					</a>
				</div>
			</div>
			<div class="general_canva">
				<!-- Elements here. -->
				<!-- Test Button -->
				<div class="el_button_block">
					<a href="#" class="el_button">Chat with me</a>
				</div>
			</div>
		</div>
		<div class="general_sidebar_right">
			<button type="button" class="sidebar_close" id="sidebarClose" aria-label="Close"></button>
			<div class="general_sidebar_texter">
				<div class="general_sidebar_texter_title"><?php echo LanguageManager::t('sidebar_elements'); ?></div>
				<div class="general_sidebar_texter_pretitle"><?php echo LanguageManager::t('sidebar_library'); ?></div>
			</div>
			<div class="general_sidebar_library">
				<div class="general_sidebar_element" data-element="group">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/block.svg">
					</div>
					<div class="general_sidebar_element_name"><?php echo LanguageManager::t('element_group'); ?></div>
				</div>
				<div class="general_sidebar_element" data-element="text">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/text.svg">
					</div>
					<div class="general_sidebar_element_name"><?php echo LanguageManager::t('element_text'); ?></div>
				</div>
				<div class="general_sidebar_element" data-element="button">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/click.svg">
					</div>
					<div class="general_sidebar_element_name"><?php echo LanguageManager::t('element_button'); ?></div>
				</div>
				<div class="general_sidebar_element" data-element="image">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/frame.svg">
					</div>
					<div class="general_sidebar_element_name"><?php echo LanguageManager::t('element_image'); ?></div>
				</div>
				<div class="general_sidebar_element" data-element="product">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/frame.svg">
					</div>
					<div class="general_sidebar_element_name"><?php echo LanguageManager::t('element_product'); ?></div>
				</div>
				<div class="general_sidebar_element" data-element="block">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/block.svg">
					</div>
					<div class="general_sidebar_element_name"><?php echo LanguageManager::t('element_block'); ?></div>
				</div>
				<div class="general_sidebar_element" data-element="link">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/link.svg">
					</div>
					<div class="general_sidebar_element_name"><?php echo LanguageManager::t('element_link'); ?></div>
				</div>
				<div class="general_sidebar_element" data-element="list">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/list.svg">
					</div>
					<div class="general_sidebar_element_name"><?php echo LanguageManager::t('element_list'); ?></div>
				</div>
			</div>
			<div class="general_sidebar_element full" data-element="separator">
				<div class="general_sidebar_element_icon">
					<img src="images/icons/sep.svg">
				</div>
				<div class="general_sidebar_element_name"><?php echo LanguageManager::t('element_separator'); ?></div>
			</div>
			<div class="general_sidebar_element" data-element="product-card">
				<div class="general_sidebar_element_icon">
					<img src="images/icons/block.svg">
				</div>
				<div class="general_sidebar_element_name"><?php echo LanguageManager::t('element_product_card'); ?></div>
			</div>
			<!-- Inspector Panel -->
			<div class="inspector" id="inspector" style="display:none;">
				<div class="general_sidebar_texter" style="margin-top: 8px;">
					<div class="general_sidebar_texter_title"><?php echo LanguageManager::t('sidebar_inspector'); ?></div>
					<div class="general_sidebar_texter_pretitle"><?php echo LanguageManager::t('sidebar_edit_selected'); ?></div>
				</div>
				<div class="inspector_target" id="inspectorTarget"></div>
				<div class="inspector_fields" id="inspectorFields"></div>
				<div class="inspector_actions">
					<button type="button" class="button stroke" id="inspectorDeselect"><?php echo LanguageManager::t('sidebar_deselect'); ?></button>
					<button type="button" class="button" id="inspectorRemove"><?php echo LanguageManager::t('sidebar_delete'); ?></button>
				</div>
			</div>
		</div>
	</div>
	<div class="sidebar-overlay" id="sidebarOverlay"></div>
	
	<!-- Save Project Modal -->
	<div id="saveModal" class="modal" style="display: none;">
		<div class="modal-content">
			<div class="modal-header">
				<h3><?php echo LanguageManager::t('modal_save_project'); ?></h3>
				<button type="button" class="modal-close" id="saveModalClose">&times;</button>
			</div>
			<div class="modal-body">
				<p><?php echo LanguageManager::t('modal_enter_project_name'); ?></p>
				<input type="text" id="projectName" placeholder="<?php echo LanguageManager::t('placeholder_project_name'); ?>" maxlength="100" autofocus>
				<div class="modal-actions">
					<button type="button" class="button stroke" id="saveModalCancel"><?php echo LanguageManager::t('btn_cancel'); ?></button>
					<button type="button" class="button primary" id="saveModalConfirm"><?php echo LanguageManager::t('btn_save'); ?></button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Deploy Project Modal -->
	<div id="deployModal" class="modal" style="display: none;">
		<div class="modal-content">
			<div class="modal-header">
				<h3><?php echo LanguageManager::t('modal_deploy_project'); ?></h3>
				<button type="button" class="modal-close" id="deployModalClose">&times;</button>
			</div>
			<div class="modal-body">
				<p><?php echo LanguageManager::t('modal_deploy_description'); ?></p>
				
				<div class="form-group">
					<label for="deployProjectName"><?php echo LanguageManager::t('form_project_name'); ?>:</label>
					<input type="text" id="deployProjectName" placeholder="<?php echo LanguageManager::t('placeholder_project_name'); ?>" maxlength="100">
				</div>
				
				<div class="form-group">
					<label for="deployProjectSlug"><?php echo LanguageManager::t('form_project_slug'); ?>:</label>
					<div class="input-group">
						<span class="input-prefix"><?php 
						// Generate proper username slug
						$usernameSlug = 'user';
						if (!empty($displayName)) {
							$usernameSlug = strtolower(preg_replace('/[^a-z0-9\s-]/', '', preg_replace('/\s+/', '-', trim($displayName, '-'))));
						} else {
							// Try to get username from email if name is empty
							try {
								$pdo = DatabaseConnectionProvider::getConnection();
								$stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
								$stmt->execute([$uid]);
								$user = $stmt->fetch();
								if ($user && !empty($user['email'])) {
									$email = $user['email'];
									$emailPart = explode('@', $email)[0];
									$usernameSlug = strtolower(preg_replace('/[^a-z0-9\s-]/', '', preg_replace('/\s+/', '-', trim($emailPart, '-'))));
								}
							} catch (Throwable $e) {
								// Keep default 'user' if database error
							}
						}
						echo rtrim($basePath, '/'); ?>/u/<?php echo htmlspecialchars($usernameSlug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>/</span>
						<input type="text" id="deployProjectSlug" placeholder="my-awesome-project" maxlength="50" pattern="[a-z0-9-]+">
					</div>
					<small class="form-hint">Только латинские буквы, цифры и дефисы. Полный URL: <?php echo rtrim($basePath, '/'); ?>/u/<?php echo htmlspecialchars($usernameSlug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>/[название]</small>
				</div>
				
				<div class="form-group">
					<label for="deployPrivacy"><?php echo LanguageManager::t('form_project_privacy'); ?>:</label>
					<select id="deployPrivacy">
						<option value="public"><?php echo LanguageManager::t('privacy_public'); ?></option>
						<option value="unlisted"><?php echo LanguageManager::t('privacy_unlisted'); ?></option>
						<option value="private"><?php echo LanguageManager::t('privacy_private'); ?></option>
					</select>
				</div>
				
				<div class="form-group">
					<label for="deployDescription"><?php echo LanguageManager::t('form_project_description'); ?> (<?php echo LanguageManager::t('no'); ?>):</label>
					<textarea id="deployDescription" placeholder="<?php echo LanguageManager::t('form_project_description'); ?>..." maxlength="200" rows="3"></textarea>
				</div>
				
				<div class="modal-actions">
					<button type="button" class="button stroke" id="deployModalCancel"><?php echo LanguageManager::t('btn_cancel'); ?></button>
					<button type="button" class="button primary" id="deployModalConfirm"><?php echo LanguageManager::t('btn_deploy'); ?></button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Load Project Modal -->
	<div id="loadModal" class="modal" style="display: none;">
		<div class="modal-content">
			<div class="modal-header">
				<h3><?php echo LanguageManager::t('modal_load_project'); ?></h3>
				<button type="button" class="modal-close" id="loadModalClose">&times;</button>
			</div>
			<div class="modal-body">
				<p><?php echo LanguageManager::t('msg_loading_projects'); ?>:</p>
				<div id="projectsList" class="projects-list">
					<div class="loading"><?php echo LanguageManager::t('msg_loading_projects'); ?></div>
				</div>
				<div class="modal-actions">
					<button type="button" class="button stroke" id="loadModalCancel"><?php echo LanguageManager::t('btn_cancel'); ?></button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Edit Project Modal -->
	<div id="editModal" class="modal" style="display: none;">
		<div class="modal-content">
			<div class="modal-header">
				<h3><?php echo LanguageManager::t('modal_edit_project'); ?></h3>
				<button type="button" class="modal-close" id="editModalClose">&times;</button>
			</div>
			<div class="modal-body">
				<p><?php echo LanguageManager::t('modal_edit_project'); ?>:</p>
				
				<div class="form-group">
					<label for="editProjectName"><?php echo LanguageManager::t('form_project_name'); ?>:</label>
					<input type="text" id="editProjectName" placeholder="<?php echo LanguageManager::t('placeholder_project_name'); ?>" maxlength="100">
				</div>
				
				<div class="form-group">
					<label for="editProjectDescription"><?php echo LanguageManager::t('form_project_description'); ?> (<?php echo LanguageManager::t('no'); ?>):</label>
					<textarea id="editProjectDescription" placeholder="<?php echo LanguageManager::t('form_project_description'); ?>..." maxlength="200" rows="3"></textarea>
				</div>
				
				<div class="form-group">
					<label for="editProjectPrivacy"><?php echo LanguageManager::t('form_project_privacy'); ?>:</label>
					<select id="editProjectPrivacy">
						<option value="private"><?php echo LanguageManager::t('privacy_private'); ?></option>
						<option value="unlisted"><?php echo LanguageManager::t('privacy_unlisted'); ?></option>
						<option value="public"><?php echo LanguageManager::t('privacy_public'); ?></option>
					</select>
				</div>
				
				<div class="modal-actions">
					<button type="button" class="button stroke" id="editModalCancel"><?php echo LanguageManager::t('btn_cancel'); ?></button>
					<button type="button" class="button primary" id="editModalConfirm"><?php echo LanguageManager::t('btn_save'); ?></button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- AI Helper Modal -->
	<div id="aiHelperModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; padding: 20px;">
		<div style="width: 100%; max-width: 600px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid rgba(255,255,255,0.2); overflow: hidden;">
			
			<!-- AI Thinking Preloader -->
			<div id="aiThinkingLoader" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: none; align-items: center; justify-content: center; z-index: 10;">
				<div style="text-align: center;">
					<div style="position: relative; margin-bottom: 24px;">
						<!-- Animated Brain -->
						<div style="width: 80px; height: 80px; margin: 0 auto 16px; position: relative;">
							<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.2); border-radius: 50%; animation: pulse 2s infinite;"></div>
							<div style="position: absolute; top: 8px; left: 8px; right: 8px; bottom: 8px; background: rgba(255,255,255,0.3); border-radius: 50%; animation: ping 2s infinite;"></div>
							<div style="position: absolute; top: 16px; left: 16px; right: 16px; bottom: 16px; background: rgba(255,255,255,0.4); border-radius: 50%; animation: bounce 2s infinite;"></div>
							<svg style="width: 48px; height: 48px; position: absolute; top: 16px; left: 16px; color: white; animation: pulse 2s infinite;" fill="currentColor" viewBox="0 0 24 24">
								<path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
							</svg>
						</div>
						
						<!-- Floating Dots -->
						<div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 16px;">
							<div style="width: 12px; height: 12px; background: rgba(255,255,255,0.6); border-radius: 50%; animation: thinkingPulse 1.5s ease-in-out infinite;"></div>
							<div style="width: 12px; height: 12px; background: rgba(255,255,255,0.6); border-radius: 50%; animation: thinkingPulse 1.5s ease-in-out infinite; animation-delay: 0.3s;"></div>
							<div style="width: 12px; height: 12px; background: rgba(255,255,255,0.6); border-radius: 50%; animation: thinkingPulse 1.5s ease-in-out infinite; animation-delay: 0.6s;"></div>
						</div>
					</div>
					
					<h3 style="font-size: 24px; font-weight: bold; color: white; margin: 0 0 8px 0;">🤖 AI думает...</h3>
					<p style="color: rgba(255,255,255,0.8); font-size: 14px; margin: 0 0 16px 0;" id="thinkingText">Анализирую ваш запрос</p>
					
					<!-- Progress Bar -->
					<div style="width: 256px; margin: 0 auto 16px; background: rgba(255,255,255,0.2); border-radius: 9999px; height: 8px;">
						<div id="thinkingProgress" style="background: linear-gradient(to right, #fbbf24, #f59e0b); height: 8px; border-radius: 9999px; transition: width 1s; width: 0%;"></div>
					</div>
					
					<button type="button" id="cancelThinking" style="padding: 8px 16px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 8px; font-size: 14px; cursor: pointer; transition: all 0.2s;">
						Отменить
					</button>
				</div>
			</div>
			<!-- Header -->
			<div style="position: relative; padding: 24px 24px 16px;">
				<div style="display: flex; align-items: center; gap: 16px;">
					<div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
						<img src="images/icons/stars.svg" style="width: 24px; height: 24px; filter: brightness(0) invert(1);">
					</div>
					<div style="flex: 1;">
						<h3 style="font-size: 24px; font-weight: bold; color: white; margin: 0;"><?php echo LanguageManager::t('modal_ai_helper'); ?></h3>
						<p style="color: rgba(255,255,255,0.8); font-size: 14px; margin: 4px 0 0 0;"><?php echo LanguageManager::t('ai_thinking'); ?></p>
					</div>
					<button type="button" id="aiHelperModalClose" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.6); background: none; border: none; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
						<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
						</svg>
					</button>
				</div>
			</div>

			<!-- Body -->
			<div style="padding: 0 24px 24px; display: flex; flex-direction: column; gap: 24px;">
				<!-- Intro -->
				<div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 16px; padding: 16px; border: 1px solid rgba(255,255,255,0.2);">
					<p style="color: rgba(255,255,255,0.9); font-size: 14px; line-height: 1.6; margin: 0;">
						<?php echo LanguageManager::t('ai_examples'); ?>
					</p>
				</div>

				<!-- Description Input -->
				<div style="display: flex; flex-direction: column; gap: 8px;">
					<label for="aiDescription" style="display: flex; align-items: center; gap: 8px; color: white; font-weight: 600; font-size: 14px;">
						<svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
						</svg>
						<?php echo LanguageManager::t('form_ai_description'); ?>:
					</label>
					<textarea 
						id="aiDescription" 
						rows="4"
						maxlength="1000"
						placeholder="Например: Создай лендинг для интернет-магазина одежды с заголовком, описанием товаров, кнопкой заказа и контактной информацией..."
						style="width: 100%; padding: 12px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); border-radius: 12px; color: white; resize: none; box-sizing: border-box; font-size: 14px;"
					></textarea>
					<p style="color: rgba(255,255,255,0.7); font-size: 12px; display: flex; align-items: center; gap: 4px; margin: 0;">
						<svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
						</svg>
						Чем подробнее описание, тем лучше результат. Укажите тип сайта, основные разделы, стиль и функциональность.
					</p>
				</div>

				<!-- Style Select -->
				<div style="display: flex; flex-direction: column; gap: 8px;">
					<label for="aiStyle" style="display: flex; align-items: center; gap: 8px; color: white; font-weight: 600; font-size: 14px;">
						<svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"/>
						</svg>
						<?php echo LanguageManager::t('form_ai_style'); ?>:
					</label>
					<select id="aiStyle" style="width: 100%; padding: 12px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); border-radius: 12px; color: white; box-sizing: border-box; font-size: 14px;">
						<option value="modern" style="background: #1f2937; color: white;"><?php echo LanguageManager::t('ai_style_modern'); ?></option>
						<option value="minimalist" style="background: #1f2937; color: white;"><?php echo LanguageManager::t('ai_style_minimalist'); ?></option>
						<option value="corporate" style="background: #1f2937; color: white;"><?php echo LanguageManager::t('ai_style_corporate'); ?></option>
						<option value="creative" style="background: #1f2937; color: white;"><?php echo LanguageManager::t('ai_style_creative'); ?></option>
						<option value="elegant" style="background: #1f2937; color: white;"><?php echo LanguageManager::t('ai_style_elegant'); ?></option>
					</select>
				</div>

				<!-- Examples -->
				<div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 16px; padding: 16px; border: 1px solid rgba(255,255,255,0.2);">
					<h4 style="color: white; font-weight: 600; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px; font-size: 14px;">
						<svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
						</svg>
						<?php echo LanguageManager::t('ai_examples'); ?>:
					</h4>
					<div style="display: flex; flex-wrap: wrap; gap: 8px;">
						<button type="button" onclick="document.getElementById('aiDescription').value = 'Создай лендинг для IT-компании с заголовком о наших услугах, описанием команды, портфолио проектов и формой обратной связи'" style="padding: 6px 12px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; font-size: 12px; border-radius: 9999px; cursor: pointer; transition: all 0.2s;">
							<?php echo LanguageManager::t('ai_example_it'); ?>
						</button>
						<button type="button" onclick="document.getElementById('aiDescription').value = 'Создай сайт ресторана с меню, фотографиями блюд, информацией о шеф-поваре и формой бронирования столика'" style="padding: 6px 12px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; font-size: 12px; border-radius: 9999px; cursor: pointer; transition: all 0.2s;">
							<?php echo LanguageManager::t('ai_example_restaurant'); ?>
						</button>
						<button type="button" onclick="document.getElementById('aiDescription').value = 'Создай портфолио фотографа с галереей работ, информацией обо мне, услугами и контактами'" style="padding: 6px 12px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; font-size: 12px; border-radius: 9999px; cursor: pointer; transition: all 0.2s;">
							<?php echo LanguageManager::t('ai_example_portfolio'); ?>
						</button>
					</div>
				</div>

				<!-- Actions -->
				<div style="display: flex; gap: 12px; padding-top: 8px;">
					<button type="button" id="aiHelperModalCancel" style="flex: 1; padding: 12px 24px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s;">
						<?php echo LanguageManager::t('btn_cancel'); ?>
					</button>
					<button type="button" id="aiHelperModalGenerate" style="flex: 1; padding: 12px 24px; background: linear-gradient(to right, #f97316, #dc2626); color: white; border: none; border-radius: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;">
						<svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
						</svg>
						<?php echo LanguageManager::t('ai_generate'); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
	
	<style>
		/* Language selector styles */
		.language-selector {
			position: relative;
			display: inline-block;
		}
		
		.language-current {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 8px 12px;
			background: #2A2A2E;
			border: 1px solid #3A3A40;
			border-radius: 8px;
			cursor: pointer;
			transition: all 0.2s ease;
			font-size: 14px;
			color: #EDEDED;
		}
		
		.language-current:hover {
			border-color: #6C5CE7;
			background: #3A3A40;
		}
		
		.language-flag-icon {
			width: 20px;
			height: 15px;
			object-fit: cover;
			border-radius: 2px;
		}
		
		.language-flag-text {
			font-weight: 500;
			font-size: 12px;
		}
		
		.language-arrow {
			transition: transform 0.2s ease;
		}
		
		.language-selector.open .language-arrow {
			transform: rotate(180deg);
		}
		
		.language-dropdown {
			position: absolute;
			top: 100%;
			right: 0;
			background: #2A2A2E;
			border: 1px solid #3A3A40;
			border-radius: 8px;
			box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
			min-width: 160px;
			z-index: 50;
			opacity: 0;
			visibility: hidden;
			transform: translateY(-10px);
			transition: all 0.2s ease;
		}
		
		.language-selector.open .language-dropdown {
			opacity: 1;
			visibility: visible;
			transform: translateY(0);
		}
		
		.language-option {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 10px 12px;
			color: #EDEDED;
			text-decoration: none;
			transition: background-color 0.2s ease;
			border-bottom: 1px solid #3A3A40;
		}
		
		.language-option:last-child {
			border-bottom: none;
		}
		
		.language-option:hover {
			background: #3A3A40;
		}
		
		.language-option.active {
			background: #6C5CE7;
			color: white;
		}
		
		.language-name {
			font-size: 14px;
			font-weight: 500;
		}
		
		.modal {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.7);
			z-index: 10000;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.modal-content {
			background: #16161A;
			border: 1px solid #2A2A2E;
			border-radius: 12px;
			padding: 0;
			max-width: 400px;
			width: 90%;
			box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
		}
		.modal-header {
			padding: 20px 24px 16px;
			border-bottom: 1px solid #2A2A2E;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}
		.modal-header h3 {
			margin: 0;
			font-size: 18px;
			font-weight: 600;
			color: #EDEDED;
		}
		.modal-close {
			background: none;
			border: none;
			font-size: 24px;
			color: #7A7A80;
			cursor: pointer;
			padding: 0;
			width: 32px;
			height: 32px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 6px;
			transition: all 0.2s;
		}
		.modal-close:hover {
			background: #2A2A2E;
			color: #EDEDED;
		}
		.modal-body {
			padding: 20px 24px 24px;
		}
		.modal-body p {
			margin: 0 0 16px;
			color: #B8B8BF;
			font-size: 14px;
		}
		.modal-body input {
			width: 100%;
			padding: 12px 16px;
			background: #0E0E10;
			border: 1px solid #3A3A40;
			border-radius: 8px;
			color: #EDEDED;
			font-size: 14px;
			margin-bottom: 20px;
			box-sizing: border-box;
		}
		.modal-body input:focus {
			outline: none;
			border-color: #6C5CE7;
		}
		.modal-actions {
			display: flex;
			gap: 12px;
			justify-content: flex-end;
		}
		.modal-actions .button {
			padding: 10px 20px;
			font-size: 14px;
			font-weight: 600;
		}
		
		/* Deploy Modal Styles */
		.form-group {
			margin-bottom: 20px;
		}
		.form-group label {
			display: block;
			margin-bottom: 8px;
			color: #EDEDED;
			font-size: 14px;
			font-weight: 500;
		}
		.form-group input, .form-group select, .form-group textarea {
			width: 100%;
			padding: 12px 16px;
			background: #0E0E10;
			border: 1px solid #3A3A40;
			border-radius: 8px;
			color: #EDEDED;
			font-size: 14px;
			box-sizing: border-box;
		}
		.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
			outline: none;
			border-color: #6C5CE7;
		}
		.input-group {
			display: flex;
			align-items: center;
		}
		.input-prefix {
			background: #2A2A2E;
			border: 1px solid #3A3A40;
			border-right: none;
			padding: 12px 16px;
			color: #B8B8BF;
			font-size: 14px;
			border-radius: 8px 0 0 8px;
			white-space: nowrap;
		}
		.input-group input {
			border-radius: 0 8px 8px 0;
			border-left: none;
		}
		.form-hint {
			color: #7A7A80;
			font-size: 12px;
			margin-top: 4px;
			display: block;
		}
		.form-group textarea {
			resize: vertical;
			min-height: 80px;
		}
		
		/* Load Project Modal Styles */
		.projects-list {
			max-height: 400px;
			overflow-y: auto;
			margin-bottom: 20px;
		}
		
		.project-item {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 16px;
			margin-bottom: 12px;
			background: #0E0E10;
			border: 1px solid #3A3A40;
			border-radius: 12px;
			cursor: pointer;
			transition: all 0.3s ease;
		}
		
		.project-item:hover {
			background: #16161A;
			border-color: #6C5CE7;
			transform: translateY(-2px);
			box-shadow: 0 8px 25px rgba(108, 92, 231, 0.2);
		}
		
		.project-info {
			flex: 1;
		}
		
		.project-title {
			font-size: 16px;
			font-weight: 600;
			color: #EDEDED;
			margin-bottom: 4px;
		}
		
		.project-meta {
			font-size: 12px;
			color: #7A7A80;
			display: flex;
			align-items: center;
			gap: 12px;
		}
		
		.project-actions {
			display: flex;
			gap: 8px;
		}
		
		.project-action-btn {
			padding: 8px 12px;
			background: #6C5CE7;
			color: white;
			border: none;
			border-radius: 8px;
			font-size: 12px;
			font-weight: 500;
			cursor: pointer;
			transition: all 0.3s ease;
		}
		
		.project-action-btn:hover {
			background: #5B4FCF;
			transform: translateY(-1px);
		}
		
		.project-action-btn.secondary {
			background: transparent;
			color: #B8B8BF;
			border: 1px solid #3A3A40;
		}
		
		.project-action-btn.secondary:hover {
			background: #2A2A2E;
			color: #EDEDED;
		}
		
		.loading {
			text-align: center;
			padding: 40px;
			color: #7A7A80;
			font-style: italic;
		}
		
		.empty-projects {
			text-align: center;
			padding: 40px;
			color: #7A7A80;
		}
		
		.empty-projects-icon {
			font-size: 48px;
			margin-bottom: 16px;
			opacity: 0.5;
		}
		
		/* Mobile Responsive Modal Styles */
		@media (max-width: 768px) {
			.modal-content {
				max-width: 95%;
				width: 95%;
				margin: 10px;
				max-height: 90vh;
				overflow-y: auto;
			}
			
			.modal-header {
				padding: 16px 20px 12px;
			}
			
			.modal-header h3 {
				font-size: 16px;
			}
			
			.modal-body {
				padding: 16px 20px 20px;
			}
			
			.modal-body p {
				font-size: 13px;
				margin-bottom: 12px;
			}
			
			.form-group {
				margin-bottom: 16px;
			}
			
			.form-group label {
				font-size: 13px;
				margin-bottom: 6px;
			}
			
			.form-group input,
			.form-group select,
			.form-group textarea {
				padding: 10px 12px;
				font-size: 14px;
			}
			
			.form-group textarea {
				min-height: 60px;
			}
			
			.modal-actions {
				flex-direction: column;
				gap: 8px;
			}
			
			.modal-actions .button {
				width: 100%;
				padding: 12px 16px;
				font-size: 14px;
			}
			
			.input-group {
				flex-direction: column;
			}
			
			.input-prefix {
				border-radius: 8px 8px 0 0;
				border-right: 1px solid #3A3A40;
				border-bottom: none;
				text-align: center;
				font-size: 12px;
			}
			
			.input-group input {
				border-radius: 0 0 8px 8px;
				border-left: 1px solid #3A3A40;
			}
			
			.form-hint {
				font-size: 11px;
				margin-top: 6px;
			}
			
			/* Projects list mobile */
			.projects-list {
				max-height: 300px;
			}
			
			.project-item {
				flex-direction: column;
				align-items: stretch;
				padding: 12px;
			}
			
			.project-info {
				margin-bottom: 12px;
			}
			
			.project-title {
				font-size: 14px;
				margin-bottom: 6px;
			}
			
			.project-meta {
				font-size: 11px;
				flex-direction: column;
				gap: 4px;
				align-items: flex-start;
			}
			
			.project-actions {
				justify-content: stretch;
				gap: 8px;
			}
			
			.project-action-btn {
				flex: 1;
				padding: 10px 12px;
				font-size: 12px;
			}
		}
		
		@media (max-width: 480px) {
			.modal-content {
				max-width: 98%;
				width: 98%;
				margin: 5px;
			}
			
			.modal-header {
				padding: 12px 16px 8px;
			}
			
			.modal-body {
				padding: 12px 16px 16px;
			}
			
			.project-item {
				padding: 10px;
			}
			
			.project-actions {
				flex-direction: column;
			}
			
			.project-action-btn {
				width: 100%;
			}
		}
		
		/* Logout button styles */
		.general_sidebar_left_logout .general_sidebar_nav_li {
			text-decoration: none;
			color: inherit;
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 12px 16px;
			border-radius: 8px;
			transition: all 0.3s ease;
		}
		
		.general_sidebar_left_logout .general_sidebar_nav_li:hover {
			background: rgba(255, 255, 255, 0.1);
			transform: translateX(4px);
		}
		
		.general_sidebar_left_logout .general_sidebar_nav_li:active {
			transform: translateX(2px);
		}
		
		/* AI Helper Button Styles */
		.ai-helper-btn {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
			position: relative;
			overflow: hidden;
			animation: aiPulse 2s ease-in-out infinite;
			box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
		}
		
		.ai-helper-btn:hover {
			transform: scale(1.15) !important;
			box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6) !important;
			animation: aiPulse 1s ease-in-out infinite;
		}
		
		.ai-helper-btn:active {
			transform: scale(1.05) !important;
		}
		
		.ai-helper-icon {
			width: 18px;
			height: 18px;
			filter: brightness(0) invert(1);
			animation: pulse 2s infinite;
		}
		
		/* Drag and Drop Styles */
		.dragging {
			opacity: 0.5;
			transform: rotate(2deg);
			transition: all 0.2s ease;
		}
		
		.drop-target {
			background: rgba(102, 126, 234, 0.1);
			border: 2px dashed rgba(102, 126, 234, 0.3);
			border-radius: 8px;
		}
		
		.drop-indicator {
			height: 2px !important;
			background: #667eea !important;
			margin: 2px 0 !important;
			border-radius: 1px !important;
			opacity: 0.8 !important;
			animation: dropIndicatorPulse 1s ease-in-out infinite;
		}
		
		@keyframes dropIndicatorPulse {
			0%, 100% { opacity: 0.8; }
			50% { opacity: 1; }
		}
		
		/* AI Thinking Loader Animations */
		@keyframes thinkingPulse {
			0%, 100% { 
				transform: scale(1);
				opacity: 0.7;
			}
			50% { 
				transform: scale(1.1);
				opacity: 1;
			}
		}
		
		@keyframes thinkingRotate {
			0% { transform: rotate(0deg); }
			100% { transform: rotate(360deg); }
		}
		
		@keyframes thinkingFloat {
			0%, 100% { transform: translateY(0px); }
			50% { transform: translateY(-10px); }
		}
		
		.thinking-brain {
			animation: thinkingFloat 2s ease-in-out infinite;
		}
		
		.thinking-dots {
			animation: thinkingPulse 1.5s ease-in-out infinite;
		}
		
		.ai-helper-btn::before {
			content: '';
			position: absolute;
			top: -50%;
			left: -50%;
			width: 200%;
			height: 200%;
			background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
				box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
			}
			50% {
				box-shadow: 0 4px 25px rgba(102, 126, 234, 0.8);
			}
		}
		
		@keyframes aiShine {
			0% {
				transform: translateX(-100%) translateY(-100%) rotate(45deg);
			}
			100% {
				transform: translateX(100%) translateY(100%) rotate(45deg);
			}
		}
		
		/* AI Helper Modal Styles */
		.ai-helper-modal {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border: none;
			color: white;
		}
		
		.ai-helper-header {
			background: rgba(255, 255, 255, 0.1);
			border-bottom: 1px solid rgba(255, 255, 255, 0.2);
		}
		
		.ai-helper-title {
			display: flex;
			align-items: center;
			gap: 16px;
		}
		
		.ai-helper-title h3 {
			color: white;
			margin: 0;
			font-size: 24px;
			font-weight: 700;
		}
		
		.ai-helper-subtitle {
			color: rgba(255, 255, 255, 0.8);
			font-size: 14px;
			margin: 4px 0 0 0;
		}
		
		.ai-helper-icon-large {
			display: flex;
			align-items: center;
			justify-content: center;
			filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
		}
		
		.ai-helper-intro {
			background: rgba(255, 255, 255, 0.1);
			padding: 16px;
			border-radius: 12px;
			margin-bottom: 24px;
		}
		
		.ai-helper-intro p {
			color: rgba(255, 255, 255, 0.9);
			margin: 0;
		}
		
		.ai-helper-modal .form-group label {
			color: white;
			display: flex;
			align-items: center;
			gap: 8px;
			font-weight: 600;
		}
		
		.label-icon {
			font-size: 16px;
		}
		
		.ai-helper-modal .form-group input,
		.ai-helper-modal .form-group textarea,
		.ai-helper-modal .form-group select {
			background: rgba(255, 255, 255, 0.15);
			border: 1px solid rgba(255, 255, 255, 0.3);
			color: white;
			backdrop-filter: blur(10px);
		}
		
		.ai-helper-modal .form-group input::placeholder,
		.ai-helper-modal .form-group textarea::placeholder {
			color: rgba(255, 255, 255, 0.7);
		}
		
		.ai-helper-modal .form-group input:focus,
		.ai-helper-modal .form-group textarea:focus,
		.ai-helper-modal .form-group select:focus {
			border-color: rgba(255, 255, 255, 0.6);
			background: rgba(255, 255, 255, 0.2);
		}
		
		.ai-helper-modal .form-hint {
			color: rgba(255, 255, 255, 0.8);
		}
		
		.ai-examples {
			background: rgba(255, 255, 255, 0.1);
			padding: 20px;
			border-radius: 12px;
			margin: 20px 0;
		}
		
		.ai-examples h4 {
			color: white;
			margin: 0 0 16px 0;
			font-size: 16px;
		}
		
		.example-buttons {
			display: flex;
			gap: 12px;
			flex-wrap: wrap;
		}
		
		.example-btn {
			background: rgba(255, 255, 255, 0.2);
			border: 1px solid rgba(255, 255, 255, 0.3);
			color: white;
			padding: 8px 16px;
			border-radius: 20px;
			font-size: 12px;
			cursor: pointer;
			transition: all 0.3s ease;
			backdrop-filter: blur(10px);
		}
		
		.example-btn:hover {
			background: rgba(255, 255, 255, 0.3);
			transform: translateY(-2px);
		}
		
		.ai-generate-btn {
			background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%) !important;
			border: none !important;
			position: relative;
			overflow: hidden;
		}
		
		.ai-generate-btn:hover {
			background: linear-gradient(135deg, #ff5252 0%, #d63031 100%) !important;
			transform: translateY(-2px);
			box-shadow: 0 8px 25px rgba(238, 90, 36, 0.4);
		}
		
		.ai-generate-btn .btn-icon {
			margin-right: 8px;
		}
		
		.ai-helper-modal .modal-close {
			color: rgba(255, 255, 255, 0.8);
		}
		
		.ai-helper-modal .modal-close:hover {
			color: white;
			background: rgba(255, 255, 255, 0.2);
		}
		
		/* AI Helper Animations */
		@keyframes thinkingPulse {
			0%, 100% { opacity: 0.4; transform: scale(1); }
			50% { opacity: 1; transform: scale(1.2); }
		}
		
		@keyframes pulse {
			0%, 100% { opacity: 1; }
			50% { opacity: 0.5; }
		}
		
		@keyframes ping {
			75%, 100% { transform: scale(2); opacity: 0; }
		}
		
		@keyframes bounce {
			0%, 100% { transform: translateY(-25%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
			50% { transform: none; animation-timing-function: cubic-bezier(0,0,0.2,1); }
		}
		
		/* Hover effects for AI Helper buttons */
		#aiHelperModal button:hover {
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
		}
		
		#aiHelperModal textarea:focus,
		#aiHelperModal select:focus {
			background: rgba(255,255,255,0.2) !important;
			border-color: rgba(255,255,255,0.5) !important;
			outline: none;
		}
		
		/* AI Helper spacing is now handled with inline styles */
		
		/* Fix alert spacing issues */
		.alert {
			margin: 1rem 0 !important;
			padding: 1rem !important;
		}
		
		/* Fix general spacing issues */
		* {
			box-sizing: border-box;
		}
		
		/* Ensure proper spacing for all elements */
		body {
			line-height: 1.6;
		}
		
		/* Fix modal spacing */
		.modal-content {
			margin: 1rem !important;
			padding: 1.5rem !important;
		}
	</style>
	
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="scripts/main.js?v=<?php echo time(); ?>"></script>
	<script>
		// Global variables
		let currentProjectId = null;
		let isProjectLoaded = false;
		
		
		// Global functions for project management with improved error handling
		window.loadProject = async function(projectId) {
			try {
				// Validate project ID
				if (!projectId || isNaN(projectId) || projectId <= 0) {
					throw new Error('Неверный ID проекта');
				}
				
				console.log('Loading project ID:', projectId);
				showNotification('Загрузка проекта...', 'info');
				
				const response = await fetch(`api/project_load_editor.php?id=${projectId}`, {
					method: 'GET',
					credentials: 'include',
					headers: {
						'Accept': 'application/json',
						'Cache-Control': 'no-cache'
					}
				});
				
				console.log('Load project response status:', response.status);
				
				if (!response.ok) {
					if (response.status === 401) {
						window.location.href = 'login.php';
						return;
					}
					
					if (response.status === 404) {
						throw new Error('Проект не найден или у вас нет доступа к нему');
					}
					
					let errorText = 'Неизвестная ошибка';
					try {
						const errorData = await response.json();
						errorText = errorData.error || errorText;
					} catch (e) {
						errorText = await response.text() || errorText;
					}
					
					console.error('Load project API error:', errorText);
					throw new Error(`Ошибка загрузки (${response.status}): ${errorText}`);
				}
				
				const data = await response.json();
				console.log('Load project data:', data);
				
				if (!data.ok) {
					throw new Error(data.error || 'Неизвестная ошибка API');
				}
				
				if (!data.project || !data.project.id) {
					throw new Error('Получены некорректные данные проекта');
				}
				
				// Clear current canvas
				$('.general_canva').empty();
				
				// Load project content with validation
				const canvasContent = data.project.canvas || '';
				if (canvasContent.trim()) {
					try {
						$('.general_canva').html(canvasContent);
					} catch (e) {
						console.error('Error setting canvas content:', e);
						// Fallback to safe content
						$('.general_canva').html('<div class="el_text_block" data-id="1"><div class="el_text" style="font-size: 32px; font-weight: 700; color: var(--corp);">Проект загружен</div></div>');
					}
				} else {
					// Empty project - add welcome message
					$('.general_canva').html('<div class="el_text_block" data-id="1"><div class="el_text" style="font-size: 32px; font-weight: 700; color: var(--corp);">Добро пожаловать!</div></div>');
				}
				
				// Save project state to localStorage
				saveProjectState(data.project.id, data.project.title, canvasContent);
				
				// Set current project ID and update UI state
				currentProjectId = data.project.id;
				isProjectLoaded = true;
				
				// Hide save button and show edit button
				const saveBtn = document.getElementById('saveBtn');
				const editBtn = document.getElementById('editBtn');
				if (saveBtn) saveBtn.style.display = 'none';
				if (editBtn) editBtn.style.display = 'block';
				
				// Close modal if open
				const loadModal = document.getElementById('loadModal');
				if (loadModal) {
					loadModal.style.display = 'none';
				}
				
				// Show success notification
				showNotification(`Проект "${data.project.title}" успешно загружен!`, 'success');
				
				// Update page title
				const baseTitle = document.title.split(' — ')[1] || 'Builder';
				document.title = `${data.project.title} — ${baseTitle}`;
				
				// Reinitialize draggable elements after a short delay
				setTimeout(() => {
					if (window.initDraggable) {
						window.initDraggable();
					}
				}, 100);
				
			} catch (error) {
				console.error('Error loading project:', error);
				showNotification('Ошибка загрузки проекта: ' + error.message, 'error');
				
				// Close modal on error
				const loadModal = document.getElementById('loadModal');
				if (loadModal) {
					loadModal.style.display = 'none';
				}
			}
		};
		
		window.viewProject = function(slug) {
			window.open(`view.php?slug=${encodeURIComponent(slug)}`, '_blank');
		};
		
		// Function to reset UI state for new project
		window.resetProjectState = function() {
			currentProjectId = null;
			isProjectLoaded = false;
			clearProjectState();
			document.getElementById('saveBtn').style.display = 'block';
			document.getElementById('editBtn').style.display = 'none';
			document.title = document.title.split(' — ')[1] || 'Builder';
		};
		
		// Auto-load project if load parameter is present
		$(document).ready(function() {
			const urlParams = new URLSearchParams(window.location.search);
			const loadProjectId = urlParams.get('load');
			
			if (loadProjectId) {
				// Remove load parameter from URL without page reload
				const newUrl = window.location.pathname;
				window.history.replaceState({}, document.title, newUrl);
				
				// Load the project
				loadProject(parseInt(loadProjectId));
			}
			
			// Logout button handler
			$('#logoutBtn').on('click', function(e) {
				e.preventDefault();
				
				// Check for unsaved changes
				const projectState = getProjectState();
				if (projectState && projectState.hasUnsavedChanges) {
					const confirmLogout = confirm('У вас есть несохраненные изменения. Вы уверены, что хотите выйти?');
					if (!confirmLogout) {
						return false;
					}
				}
				
				// Show logout confirmation
				const confirmExit = confirm('Вы уверены, что хотите выйти из системы?');
				if (confirmExit) {
					// Clear project state
					clearProjectState();
					
					// Show loading notification
					showNotification('Выход из системы...', 'info');
					
					// Perform AJAX logout
					performLogout();
				}
				
				return false;
			});
			
			// AJAX logout function
			async function performLogout() {
				try {
					const response = await fetch('api/logout.php', {
						method: 'POST',
						credentials: 'include',
						headers: {
							'Content-Type': 'application/json',
						}
					});
					
					const data = await response.json();
					
					if (data.ok) {
						// Show success message
						showNotification('Вы успешно вышли из системы', 'success');
						
						// Redirect after a short delay
						setTimeout(() => {
							window.location.href = data.redirect_url || 'login.php?logout=success';
						}, 1000);
					} else {
						// Fallback to regular logout
						window.location.href = 'logout.php';
					}
				} catch (error) {
					console.error('Logout error:', error);
					// Fallback to regular logout
					window.location.href = 'logout.php';
				}
			}
		});
		
		// serializeCanvasOuterHtml function is defined in main.js

		// showNotification function is defined in main.js

		// saveProject function is defined in main.js
		
		// Deploy project functionality
		async function deployProject(projectData) {
			try {
				// Check if this is a duplicate deploy attempt
				if (isProjectLoaded && currentProjectId) {
					const confirmDeploy = confirm('Этот проект уже загружен. Вы хотите обновить существующий деплой или создать новый?');
					if (!confirmDeploy) {
						return;
					}
				}
				
				const canvas = serializeCanvasOuterHtml();
				const form = new FormData();
				form.append('title', projectData.title);
				form.append('slug', projectData.slug);
				form.append('privacy', projectData.privacy);
				form.append('description', projectData.description || '');
				form.append('canvas', canvas);
				
				// If we have a current project, include its ID for updating
				if (currentProjectId) {
					form.append('id', currentProjectId);
				}
				
				const apiUrl = new URL('api/project_deploy.php', document.baseURI).pathname;
				const res = await fetch(apiUrl, {
					method: 'POST',
					credentials: 'include',
					body: form
				});
				
				if (!res.ok) {
					if (res.status === 401) { window.location.href = 'login.php'; return; }
					const txt = await res.text();
					alert('Ошибка деплоя (HTTP ' + res.status + '): ' + txt);
					return;
				}
				
				const data = await res.json();
				if (data.ok) { 
					// Update project state
					currentProjectId = data.id;
					isProjectLoaded = true;
					
					// Save project state to localStorage
					saveProjectState(data.id, projectData.title, canvas);
					
					// Hide save button and show edit button
					document.getElementById('saveBtn').style.display = 'none';
					document.getElementById('editBtn').style.display = 'block';
					
					showNotification('Проект успешно задеплоен!', 'success');
					// Show the public URL
					setTimeout(() => {
						showDeploySuccess(data.public_url, data.stats_url);
					}, 1000);
				}
				else { 
					showNotification('Ошибка деплоя: ' + (data.error || 'unknown'), 'error');
				}
			} catch (e) {
				showNotification('Ошибка сети: ' + e.message, 'error');
			}
		}
		
		function showDeploySuccess(publicUrl, statsUrl) {
			const modal = $(`
				<div class="modal" style="display: flex;">
					<div class="modal-content">
						<div class="modal-header">
							<h3>🎉 Деплой успешен!</h3>
							<button type="button" class="modal-close" onclick="$(this).closest('.modal').remove()">&times;</button>
						</div>
						<div class="modal-body">
							<p>Ваш проект теперь доступен по адресу:</p>
							<div class="url-display">
								<input type="text" value="${publicUrl}" readonly onclick="this.select()" style="width: 100%; padding: 12px; background: #0E0E10; border: 1px solid #3A3A40; border-radius: 8px; color: #EDEDED; font-size: 14px; margin-bottom: 16px;">
								<button type="button" class="button primary" onclick="navigator.clipboard.writeText('${publicUrl}').then(() => showNotification('Ссылка скопирована!', 'success'))">📋 Копировать ссылку</button>
							</div>
							<div class="modal-actions" style="margin-top: 20px;">
								<button type="button" class="button stroke" onclick="$(this).closest('.modal').remove()">Закрыть</button>
								<button type="button" class="button primary" onclick="window.open('${publicUrl}', '_blank')">👀 Открыть проект</button>
								<button type="button" class="button stroke" onclick="window.open('${statsUrl}', '_blank')">📊 Статистика</button>
							</div>
						</div>
					</div>
				</div>
			`);
			$('body').append(modal);
		}
		
		// Save project functionality
		$(document).ready(function() {
			const saveBtn = document.getElementById('saveBtn');
			const saveModal = document.getElementById('saveModal');
			const saveModalClose = document.getElementById('saveModalClose');
			const saveModalCancel = document.getElementById('saveModalCancel');
			const saveModalConfirm = document.getElementById('saveModalConfirm');
			const projectNameInput = document.getElementById('projectName');
			
			// Deploy modal elements
			const deployBtn = document.getElementById('deployBtn');
			const deployModal = document.getElementById('deployModal');
			const deployModalClose = document.getElementById('deployModalClose');
			const deployModalCancel = document.getElementById('deployModalCancel');
			const deployModalConfirm = document.getElementById('deployModalConfirm');
			const deployProjectName = document.getElementById('deployProjectName');
			const deployProjectSlug = document.getElementById('deployProjectSlug');
			const deployPrivacy = document.getElementById('deployPrivacy');
			const deployDescription = document.getElementById('deployDescription');
			
			// Load modal elements
			const loadBtn = document.getElementById('loadBtn');
			const loadModal = document.getElementById('loadModal');
			const loadModalClose = document.getElementById('loadModalClose');
			const loadModalCancel = document.getElementById('loadModalCancel');
			const projectsList = document.getElementById('projectsList');
			
			// New project button
			const newProjectBtn = document.getElementById('newProjectBtn');
			
			// Edit modal elements
			const editBtn = document.getElementById('editBtn');
			const editModal = document.getElementById('editModal');
			const editModalClose = document.getElementById('editModalClose');
			const editModalCancel = document.getElementById('editModalCancel');
			const editModalConfirm = document.getElementById('editModalConfirm');
			const editProjectName = document.getElementById('editProjectName');
			const editProjectDescription = document.getElementById('editProjectDescription');
			const editProjectPrivacy = document.getElementById('editProjectPrivacy');
			
			// AI Helper modal elements
			const aiHelperBtn = document.getElementById('aiHelperBtn');
			const aiHelperModal = document.getElementById('aiHelperModal');
			const aiHelperModalClose = document.getElementById('aiHelperModalClose');
			const aiHelperModalCancel = document.getElementById('aiHelperModalCancel');
			const aiHelperModalGenerate = document.getElementById('aiHelperModalGenerate');
			const aiDescription = document.getElementById('aiDescription');
			const aiStyle = document.getElementById('aiStyle');
			
			// Current project tracking (declared globally above)
			
			// Open save modal
			if (saveBtn) {
				saveBtn.addEventListener('click', function(e) {
					e.preventDefault();
					console.log('Save button clicked!');
					saveModal.style.display = 'flex';
					projectNameInput.focus();
				});
			}
			
			// Open deploy modal
			if (deployBtn) {
				deployBtn.addEventListener('click', function(e) {
					e.preventDefault();
					console.log('Deploy button clicked!');
					deployModal.style.display = 'flex';
					deployProjectName.focus();
					
					// Auto-generate slug from project name
					deployProjectName.addEventListener('input', function() {
						const slug = this.value
							.toLowerCase()
							.replace(/[^a-z0-9\s-]/g, '')
							.replace(/\s+/g, '-')
							.replace(/-+/g, '-')
							.trim();
						deployProjectSlug.value = slug;
					});
				});
			}
			
			// Open load modal
			if (loadBtn) {
				loadBtn.addEventListener('click', function(e) {
					e.preventDefault();
					console.log('Load button clicked!');
					loadModal.style.display = 'flex';
					loadProjects();
				});
			}
			
			// New project button
			if (newProjectBtn) {
				newProjectBtn.addEventListener('click', function(e) {
					e.preventDefault();
					console.log('New project button clicked!');
					
					// Check for unsaved changes
					const projectState = getProjectState();
					if (projectState && projectState.hasUnsavedChanges) {
						const confirmNew = confirm('У вас есть несохраненные изменения. Вы уверены, что хотите создать новый проект?');
						if (!confirmNew) {
							return;
						}
					}
					
					// Clear canvas and reset state
					$('.general_canva').empty();
					resetProjectState();
					showNotification('Создан новый проект', 'success');
				});
			}
			
			// Open edit modal
			if (editBtn) {
				editBtn.addEventListener('click', function(e) {
					e.preventDefault();
					console.log('Edit button clicked!');
					if (currentProjectId) {
						loadProjectForEdit(currentProjectId);
					} else {
						showNotification('Сначала загрузите проект для редактирования', 'error');
					}
				});
			}
			
			// Open AI Helper modal
			if (aiHelperBtn) {
				aiHelperBtn.addEventListener('click', function(e) {
					e.preventDefault();
					console.log('AI Helper button clicked!');
					aiHelperModal.style.display = 'flex';
					aiDescription.focus();
				});
			}
			
			// Close save modal functions
			function closeSaveModal() {
				saveModal.style.display = 'none';
				projectNameInput.value = '';
			}
			
			if (saveModalClose) {
				saveModalClose.addEventListener('click', closeSaveModal);
			}
			if (saveModalCancel) {
				saveModalCancel.addEventListener('click', closeSaveModal);
			}
			
			// Close deploy modal functions
			function closeDeployModal() {
				deployModal.style.display = 'none';
				deployProjectName.value = '';
				deployProjectSlug.value = '';
				deployPrivacy.value = 'public';
				deployDescription.value = '';
			}
			
			// Close load modal functions
			function closeLoadModal() {
				loadModal.style.display = 'none';
			}
			
			// Close edit modal functions
			function closeEditModal() {
				editModal.style.display = 'none';
			}
			
			// Close AI Helper modal functions
			function closeAiHelperModal() {
				aiHelperModal.style.display = 'none';
				aiDescription.value = '';
				aiStyle.value = 'modern';
			}
			
			if (deployModalClose) {
				deployModalClose.addEventListener('click', closeDeployModal);
			}
			if (deployModalCancel) {
				deployModalCancel.addEventListener('click', closeDeployModal);
			}
			
			if (loadModalClose) {
				loadModalClose.addEventListener('click', closeLoadModal);
			}
			if (loadModalCancel) {
				loadModalCancel.addEventListener('click', closeLoadModal);
			}
			
			if (editModalClose) {
				editModalClose.addEventListener('click', closeEditModal);
			}
			if (editModalCancel) {
				editModalCancel.addEventListener('click', closeEditModal);
			}
			
			if (aiHelperModalClose) {
				aiHelperModalClose.addEventListener('click', closeAiHelperModal);
			}
			if (aiHelperModalCancel) {
				aiHelperModalCancel.addEventListener('click', closeAiHelperModal);
			}
			
			// Close on overlay click
			if (saveModal) {
				saveModal.addEventListener('click', function(e) {
					if (e.target === saveModal) {
						closeSaveModal();
					}
				});
			}
			
			if (deployModal) {
				deployModal.addEventListener('click', function(e) {
					if (e.target === deployModal) {
						closeDeployModal();
					}
				});
			}
			
			if (loadModal) {
				loadModal.addEventListener('click', function(e) {
					if (e.target === loadModal) {
						closeLoadModal();
					}
				});
			}
			
			if (editModal) {
				editModal.addEventListener('click', function(e) {
					if (e.target === editModal) {
						closeEditModal();
					}
				});
			}
			
			if (aiHelperModal) {
				aiHelperModal.addEventListener('click', function(e) {
					if (e.target === aiHelperModal) {
						closeAiHelperModal();
					}
				});
			}
			
			// Save project
			if (saveModalConfirm) {
				saveModalConfirm.addEventListener('click', function() {
					const projectName = projectNameInput.value.trim();
					if (!projectName) {
						alert('Пожалуйста, введите название проекта');
						projectNameInput.focus();
						return;
					}
					
					closeSaveModal();
					
					// Call the actual save function
					console.log('Calling saveProject with:', projectName);
					saveProject(projectName);
				});
			}
			
			// Deploy project
			if (deployModalConfirm) {
				deployModalConfirm.addEventListener('click', function() {
					const projectData = {
						title: deployProjectName.value.trim(),
						slug: deployProjectSlug.value.trim(),
						privacy: deployPrivacy.value,
						description: deployDescription.value.trim()
					};
					
					if (!projectData.title) {
						alert('Пожалуйста, введите название проекта');
						deployProjectName.focus();
						return;
					}
					
					if (!projectData.slug) {
						alert('Пожалуйста, введите URL-адрес');
						deployProjectSlug.focus();
						return;
					}
					
					// Validate slug format
					if (!/^[a-z0-9-]+$/.test(projectData.slug)) {
						alert('URL-адрес может содержать только латинские буквы, цифры и дефисы');
						deployProjectSlug.focus();
						return;
					}
					
					closeDeployModal();
					
					// Call the actual deploy function
					console.log('Calling deployProject with:', projectData);
					deployProject(projectData);
				});
			}
			
			// Enter key to save
			if (projectNameInput) {
				projectNameInput.addEventListener('keypress', function(e) {
					if (e.key === 'Enter') {
						saveModalConfirm.click();
					}
				});
			}
			
			// Enter key to deploy
			if (deployProjectName) {
				deployProjectName.addEventListener('keypress', function(e) {
					if (e.key === 'Enter') {
						deployModalConfirm.click();
					}
				});
			}
			
			// Load projects function with improved error handling
			async function loadProjects() {
				try {
					console.log('Loading projects...');
					projectsList.innerHTML = '<div class="loading">Загрузка проектов...</div>';
					
					const response = await fetch('api/projects_list.php', {
						method: 'GET',
						credentials: 'include',
						headers: {
							'Accept': 'application/json',
							'Cache-Control': 'no-cache'
						}
					});
					
					console.log('Projects response status:', response.status);
					
					if (!response.ok) {
						if (response.status === 401) {
							window.location.href = 'login.php';
							return;
						}
						
						let errorText = 'Неизвестная ошибка';
						try {
							const errorData = await response.json();
							errorText = errorData.error || errorText;
						} catch (e) {
							errorText = await response.text() || errorText;
						}
						
						console.error('Projects API error:', errorText);
						throw new Error(`Ошибка загрузки (${response.status}): ${errorText}`);
					}
					
					const data = await response.json();
					console.log('Projects data:', data);
					
					if (!data.ok) {
						throw new Error(data.error || 'Неизвестная ошибка API');
					}
					
					if (data.projects && data.projects.length > 0) {
						projectsList.innerHTML = data.projects.map(project => {
							// Validate project data
							if (!project.id || !project.title) {
								console.warn('Invalid project data:', project);
								return '';
							}
							
							const safeTitle = escapeHtml(project.title);
							const safeSlug = escapeHtml(project.slug || '');
							const formattedDate = project.formatted_date || 
								(project.updated_at ? new Date(project.updated_at).toLocaleDateString('ru-RU') : 'Неизвестно');
							
							return `
								<div class="project-item" data-project-id="${project.id}">
									<div class="project-info">
										<div class="project-title">${safeTitle}</div>
										<div class="project-meta">
											<span>${project.privacy_icon} ${project.privacy}</span>
											${project.has_deploy ? `<span>👁️ ${project.view_count || 0} просмотров</span>` : ''}
											<span>📅 ${formattedDate}</span>
										</div>
									</div>
									<div class="project-actions">
										<button class="project-action-btn" onclick="loadProject(${project.id})" title="Загрузить проект в редактор">Загрузить</button>
										${project.has_deploy ? `<button class="project-action-btn secondary" onclick="viewProject('${safeSlug}')" title="Открыть опубликованный проект">Просмотр</button>` : ''}
									</div>
								</div>
							`;
						}).filter(Boolean).join('');
						
						if (!projectsList.innerHTML.trim()) {
							throw new Error('Нет валидных проектов для отображения');
						}
					} else {
						projectsList.innerHTML = `
							<div class="empty-projects">
								<div class="empty-projects-icon">📁</div>
								<p>У вас пока нет сохраненных проектов</p>
								<small style="color: #7A7A80; margin-top: 8px;">Создайте новый проект или сохраните текущий</small>
							</div>
						`;
					}
				} catch (error) {
					console.error('Error loading projects:', error);
					projectsList.innerHTML = `
						<div class="loading" style="color: #F44336;">
							<div style="margin-bottom: 12px;">❌ Ошибка загрузки проектов</div>
							<div style="font-size: 12px; color: #7A7A80;">${error.message}</div>
							<button onclick="loadProjects()" style="margin-top: 12px; padding: 8px 16px; background: #6C5CE7; color: white; border: none; border-radius: 6px; cursor: pointer;">Повторить</button>
						</div>
					`;
				}
			}
			
			// Helper function to escape HTML
			function escapeHtml(text) {
				const div = document.createElement('div');
				div.textContent = text;
				return div.innerHTML;
			}
			
			// Note: loadProject and viewProject are now defined as global functions above
			
			// Load project for editing
			async function loadProjectForEdit(projectId) {
				try {
					const response = await fetch(`api/project_load_editor.php?id=${projectId}`, {
						method: 'GET',
						credentials: 'include'
					});
					
					if (!response.ok) {
						throw new Error('Failed to load project');
					}
					
					const data = await response.json();
					
					if (data.ok) {
						// Fill edit form
						editProjectName.value = data.project.title;
						editProjectDescription.value = data.project.description || '';
						editProjectPrivacy.value = data.project.privacy;
						
						// Show edit modal
						editModal.style.display = 'flex';
					} else {
						showNotification('Ошибка загрузки проекта: ' + (data.error || 'unknown'), 'error');
					}
				} catch (error) {
					console.error('Error loading project for edit:', error);
					showNotification('Ошибка загрузки проекта: ' + error.message, 'error');
				}
			}
			
			// Update project function
			async function updateProject() {
				try {
					const formData = new FormData();
					formData.append('id', currentProjectId);
					formData.append('title', editProjectName.value.trim());
					formData.append('description', editProjectDescription.value.trim());
					formData.append('privacy', editProjectPrivacy.value);
					formData.append('canvas', serializeCanvasOuterHtml());
					
					const response = await fetch('api/project_update.php', {
						method: 'POST',
						credentials: 'include',
						body: formData
					});
					
					if (!response.ok) {
						throw new Error('Failed to update project');
					}
					
					const data = await response.json();
					
					if (data.ok) {
						closeEditModal();
						showNotification('Проект успешно обновлен!', 'success');
						
						// Update page title
						document.title = `${editProjectName.value} — ${document.title.split(' — ')[1] || 'Builder'}`;
					} else {
						showNotification('Ошибка обновления проекта: ' + (data.error || 'unknown'), 'error');
					}
				} catch (error) {
					console.error('Error updating project:', error);
					showNotification('Ошибка обновления проекта: ' + error.message, 'error');
				}
			}
			
			// Edit modal confirm handler
			if (editModalConfirm) {
				editModalConfirm.addEventListener('click', function() {
					const projectName = editProjectName.value.trim();
					
					if (!projectName) {
						showNotification('Пожалуйста, введите название проекта', 'error');
						editProjectName.focus();
						return;
					}
					
					updateProject();
				});
			}
			
			// AI Helper generate handler
			if (aiHelperModalGenerate) {
				aiHelperModalGenerate.addEventListener('click', function() {
					const description = aiDescription.value.trim();
					const style = aiStyle.value;
					
					if (!description) {
						showNotification('Пожалуйста, опишите, какой сайт вы хотите создать', 'error');
						aiDescription.focus();
						return;
					}
					
					closeAiHelperModal();
					generateWithAI(description, style);
				});
			}
		});
		
		// AI Helper function with improved error handling
		async function generateWithAI(description, style) {
			// Store original button state outside try block
			const generateBtn = document.getElementById('aiHelperModalGenerate');
			const originalText = generateBtn ? generateBtn.innerHTML : '';
			
			try {
				// Show thinking loader
				showAIThinkingLoader();
				
				// Show loading state on button
				if (generateBtn) {
					generateBtn.innerHTML = '<span class="btn-icon">⏳</span>Генерация...';
					generateBtn.disabled = true;
				}
				
				const response = await fetch('api/ai_generate.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json'
					},
					credentials: 'include',
					body: JSON.stringify({
						description: description,
						style: style
					})
				});
				
				if (!response.ok) {
					if (response.status === 401) {
						window.location.href = 'login.php';
						return;
					}
					
					let errorText = 'Неизвестная ошибка';
					try {
						const errorData = await response.json();
						errorText = errorData.error || errorText;
						console.error('API Error Response:', errorData);
					} catch (e) {
						try {
							errorText = await response.text() || errorText;
							console.error('API Error Text:', errorText);
						} catch (e2) {
							console.error('Failed to read error response:', e2);
						}
					}
					
					throw new Error(`Ошибка API (${response.status}): ${errorText}`);
				}
				
				const data = await response.json();
				console.log('AI generation response:', data);
				
				if (data.ok && data.elements && Array.isArray(data.elements)) {
					// Clear current canvas
					$('.general_canva').empty();
					
					// Add generated elements to canvas with animation
					let addedCount = 0;
					data.elements.forEach((element, index) => {
						setTimeout(() => {
							try {
								addElementToCanvas(element.type, null, {
									content: element.content,
									style: element.style,
									aiGenerated: true
								});
								addedCount++;
								
								// Show completion message after all elements are added
								if (addedCount === data.elements.length) {
									setTimeout(() => {
										showNotification(`✅ AI создал ${addedCount} элементов для вашего сайта!`, 'success');
									}, 500);
								}
							} catch (e) {
								console.error('Error adding AI element:', e);
							}
						}, index * 300);
					});
					
					// Mark project as changed
					if (window.markProjectAsChanged) {
						markProjectAsChanged();
					}
					
				} else {
					throw new Error(data.error || 'AI не смог сгенерировать элементы');
				}
			} catch (error) {
				console.error('AI generation error:', error);
				showNotification('❌ Ошибка генерации: ' + error.message, 'error');
			} finally {
				// Hide thinking loader
				hideAIThinkingLoader();
				
				// Restore button state
				const generateBtn = document.getElementById('aiHelperModalGenerate');
				if (generateBtn) {
					generateBtn.innerHTML = originalText;
					generateBtn.disabled = false;
				}
			}
		}
		
		// AI Thinking Loader Functions
		function showAIThinkingLoader() {
			const loader = document.getElementById('aiThinkingLoader');
			const thinkingText = document.getElementById('thinkingText');
			const progress = document.getElementById('thinkingProgress');
			
			if (loader) {
				loader.style.display = 'flex';
				
				// Animate progress bar and change messages
				let progressValue = 0;
				let messageIndex = 0;
				
				const messages = [
					'Анализирую ваш запрос...',
					'Подключаюсь к нейросети...',
					'Генерирую структуру сайта...',
					'Создаю элементы...',
					'Применяю стили...',
					'Финализирую результат...'
				];
				
				const progressInterval = setInterval(() => {
					progressValue += Math.random() * 15 + 5; // Random progress increment
					if (progressValue > 95) progressValue = 95; // Don't reach 100% until done
					
					if (progress) {
						progress.style.width = progressValue + '%';
					}
					
					// Change message every 2 seconds
					if (Math.random() > 0.7 && messageIndex < messages.length - 1) {
						messageIndex++;
						if (thinkingText) {
							thinkingText.textContent = messages[messageIndex];
						}
					}
				}, 500);
				
				// Store interval for cleanup
				loader.dataset.progressInterval = progressInterval;
			}
		}
		
		function hideAIThinkingLoader() {
			const loader = document.getElementById('aiThinkingLoader');
			const progress = document.getElementById('thinkingProgress');
			const thinkingText = document.getElementById('thinkingText');
			
			if (loader) {
				// Complete progress bar
				if (progress) {
					progress.style.width = '100%';
				}
				
				// Clear interval
				const intervalId = loader.dataset.progressInterval;
				if (intervalId) {
					clearInterval(parseInt(intervalId));
				}
				
				// Hide after a short delay to show completion
				setTimeout(() => {
					loader.style.display = 'none';
					
					// Reset for next time
					if (progress) progress.style.width = '0%';
					if (thinkingText) thinkingText.textContent = 'Анализирую ваш запрос';
				}, 800);
			}
		}
		
		// Cancel thinking handler
		document.getElementById('cancelThinking')?.addEventListener('click', function() {
			hideAIThinkingLoader();
			closeAiHelperModal();
		});
		
		// Save to Library
		document.getElementById('saveToLibraryBtn')?.addEventListener('click', function() {
			showSaveToLibraryModal();
		});
		
		// Create project from Community template
		window.createProjectFromCommunity = function(projectData) {
			// Create new project
			fetch('api/save_project.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					title: projectData.title,
					description: projectData.description,
					privacy: 'private'
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					const projectId = data.project_id;
					
					// Save elements to the new project
					const elements = projectData.elements || [];
					elements.forEach((element, index) => {
						// Remove old project_id and id from element
						delete element.project_id;
						delete element.id;
						
						// Add new project_id
						element.project_id = projectId;
						element.z_index = index;
						
						// Save element
						fetch('api/save_element.php', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
							},
							body: JSON.stringify(element)
						});
					});
					
					// Reload the page with new project
					window.location.href = `index.php?load=${projectId}`;
				} else {
					showNotification('Error creating project: ' + (data.message || 'Unknown error'), 'error');
				}
			})
			.catch(error => {
				console.error('Error:', error);
				showNotification('Error creating project!', 'error');
			});
		};
		
		// Show notification function
		function showNotification(message, type) {
			const notification = document.createElement('div');
			notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
				type === 'success' ? 'bg-green-500 text-white' :
				type === 'error' ? 'bg-red-500 text-white' :
				'bg-blue-500 text-white'
			}`;
			notification.textContent = message;
			document.body.appendChild(notification);
			
			setTimeout(() => {
				notification.remove();
			}, 3000);
		}
		
		// Show Save to Library Modal
		function showSaveToLibraryModal() {
			const modal = document.createElement('div');
			modal.id = 'communityShareModal';
			modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 9999; animation: fadeIn 0.3s ease;';
			
			modal.innerHTML = `
				<div style="background: white; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-width: 550px; width: 90%; margin: 20px; animation: slideUp 0.4s ease; overflow: hidden;">
					<!-- Header with gradient -->
					<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 32px 24px; color: white; position: relative; overflow: hidden;">
						<div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
						<div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
						<div style="position: relative; z-index: 1;">
							<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
								<div style="display: flex; align-items: center; gap: 12px;">
									<div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
										<span class="material-icons" style="color: white; font-size: 24px;">public</span>
									</div>
									<div>
										<h3 style="margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px;">Share to Community</h3>
										<p style="margin: 4px 0 0 0; font-size: 14px; opacity: 0.9;">Let others discover your amazing work</p>
									</div>
								</div>
								<button onclick="closeSaveToLibraryModal()" style="width: 36px; height: 36px; background: rgba(255,255,255,0.2); border: none; border-radius: 12px; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
									<span class="material-icons" style="font-size: 20px;">close</span>
								</button>
							</div>
						</div>
					</div>
					
					<!-- Form Content -->
					<div style="padding: 32px 24px;">
						<form id="saveTemplateForm">
							<!-- Project Description -->
							<div style="margin-bottom: 24px;">
								<label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 14px; display: flex; align-items: center; gap: 6px;">
									<span class="material-icons" style="color: #667eea; font-size: 18px;">description</span>
									Project Description
									<span style="color: #ef4444; font-size: 12px;">*</span>
								</label>
								<div style="position: relative;">
									<textarea id="templateDescription" 
										style="width: 100%; padding: 14px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 14px; resize: none; background: #f9fafb; min-height: 110px; box-sizing: border-box; transition: all 0.2s; font-family: inherit;"
										placeholder="Tell the community about your project... What problem does it solve? What makes it unique?"
										maxlength="500"
										required></textarea>
									<div style="position: absolute; bottom: 12px; right: 12px; font-size: 11px; color: #9ca3af; background: white; padding: 2px 8px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
										<span id="charCount">0</span>/500
									</div>
								</div>
								<p style="margin: 8px 0 0 0; font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px;">
									<span class="material-icons" style="font-size: 14px;">info</span>
									A good description helps others find and use your project
								</p>
							</div>
							
							<!-- Category -->
							<div style="margin-bottom: 28px;">
								<label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 14px; display: flex; align-items: center; gap: 6px;">
									<span class="material-icons" style="color: #667eea; font-size: 18px;">category</span>
									Category
									<span style="color: #ef4444; font-size: 12px;">*</span>
								</label>
								<div style="position: relative;">
									<select id="templateCategory" 
										style="width: 100%; padding: 14px 40px 14px 14px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 14px; background: #f9fafb; cursor: pointer; box-sizing: border-box; appearance: none; transition: all 0.2s; font-family: inherit;"
										required>
										<option value="">Choose a category...</option>
										<option value="landing">🚀 Landing Page</option>
										<option value="blog">📝 Blog</option>
										<option value="portfolio">💼 Portfolio</option>
										<option value="ecommerce">🛒 E-commerce</option>
										<option value="business">🏢 Business</option>
										<option value="personal">👤 Personal</option>
										<option value="general">⭐ General</option>
									</select>
									<span class="material-icons" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 20px;">arrow_drop_down</span>
								</div>
							</div>
							
							<!-- Action Buttons -->
							<div style="display: flex; gap: 12px; padding-top: 24px; border-top: 2px solid #f3f4f6;">
								<button type="button" 
									onclick="closeSaveToLibraryModal()" 
									style="flex: 1; padding: 14px; background: white; color: #6b7280; border: 2px solid #e5e7eb; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; font-family: inherit;">
									<span class="material-icons" style="font-size: 18px;">close</span>
									Cancel
								</button>
								<button type="submit" 
									style="flex: 2; padding: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); font-family: inherit;">
									<span class="material-icons" style="font-size: 18px;">public</span>
									Share to Community
								</button>
							</div>
						</form>
					</div>
				</div>
				
				<style>
					@keyframes fadeIn {
						from { opacity: 0; }
						to { opacity: 1; }
					}
					
					@keyframes slideUp {
						from { 
							opacity: 0;
							transform: translateY(20px);
						}
						to { 
							opacity: 1;
							transform: translateY(0);
						}
					}
					
					#communityShareModal textarea:focus,
					#communityShareModal select:focus {
						border-color: #667eea;
						background: white;
						outline: none;
						box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
					}
					
					#communityShareModal button:hover {
						transform: translateY(-2px);
					}
					
					#communityShareModal button:active {
						transform: translateY(0);
					}
					
					#communityShareModal button[type="submit"]:hover {
						box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
					}
					
					#communityShareModal button[type="button"]:hover {
						background: #f9fafb;
						border-color: #d1d5db;
					}
				</style>
			`;
			
			document.body.appendChild(modal);
			
			// Add character counter for textarea
			const textarea = document.getElementById('templateDescription');
			const charCount = document.getElementById('charCount');
			
			textarea.addEventListener('input', function() {
				const length = this.value.length;
				charCount.textContent = length;
				
				if (length > 500) {
					charCount.style.color = '#ef4444';
					this.style.borderColor = '#fca5a5';
				} else {
					charCount.style.color = '#9ca3af';
					this.style.borderColor = '#e5e7eb';
				}
			});
			
			// Handle form submission
			document.getElementById('saveTemplateForm').addEventListener('submit', function(e) {
				e.preventDefault();
				saveToLibrary();
			});
			
			// Close modal on background click
			modal.addEventListener('click', function(e) {
				if (e.target === modal) {
					closeSaveToLibraryModal();
				}
			});
			
			// Close modal on Escape key
			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape') {
					closeSaveToLibraryModal();
				}
			});
		}
		
		// Close Save to Library Modal
		function closeSaveToLibraryModal() {
			const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
			if (modal) {
				modal.remove();
			}
		}
		
		// Save to Library function
		function saveToLibrary() {
			const projectId = getCurrentProjectId();
			if (!projectId) {
				showNotification('No project to save!', 'error');
				return;
			}
			
			const templateName = document.getElementById('templateName').value;
			const templateDescription = document.getElementById('templateDescription').value;
			const templateCategory = document.getElementById('templateCategory').value;
			
			if (!templateName || !templateCategory) {
				showNotification('Please fill in all required fields!', 'error');
				return;
			}
			
			// Show loading
			const submitBtn = document.querySelector('#saveTemplateForm button[type="submit"]');
			const originalText = submitBtn.innerHTML;
			submitBtn.innerHTML = '<span class="material-icons animate-spin">refresh</span><span>Saving...</span>';
			submitBtn.disabled = true;
			
			// Save project to community
			fetch('api/projects_community.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					action: 'save',
					project_id: projectId,
					description: templateDescription,
					category: templateCategory
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					showNotification('Project shared to community successfully!', 'success');
					closeSaveToLibraryModal();
				} else {
					showNotification(data.message || 'Error sharing project!', 'error');
				}
			})
			.catch(error => {
				console.error('Error:', error);
				showNotification('Error sharing project!', 'error');
			})
			.finally(() => {
				submitBtn.innerHTML = originalText;
				submitBtn.disabled = false;
			});
		}
		
		// Get current project ID
		function getCurrentProjectId() {
			const urlParams = new URLSearchParams(window.location.search);
			return urlParams.get('load') || null;
		}
	</script>
	
	<!-- Ultra Simple Templates Component -->
	<?php include __DIR__ . '/components/templates_ultra_simple.php'; ?>
</body>
</html>