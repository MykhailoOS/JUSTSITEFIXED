<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
start_session_if_needed();
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
        if ($row && !empty($row['dn'])) { $displayName = htmlspecialchars($row['dn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    } catch (Throwable $e) { /* ignore */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<base href="<?php echo $basePath; ?>">
	<title><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> — Builder</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100..900&display=swap" rel="stylesheet">
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
					<a href="profile.php" class="header_profile" title="Profile"></a>
				</div>
			</div>
		</div>
	</div>
	<div class="general">
		<div class="general_sidebar_left">
			<div class="general_sidebar_left_block">
				<div class="general_sidebar_title_wrapper">
					<div class="general_sidebar_title">Dashboard</div>
				</div>
				<div class="general_sidebar_nav">
					<div class="general_sidebar_nav_li">
						<div class="general_sidebar_nav_li_icon">
							<img src="images/icons/home.svg">
						</div>
						<div class="general_sidebar_nav_li_title">Home</div>
					</div>
				</div>
			</div>
			<div class="general_sidebar_left_logout">
				<a href="logout.php" class="general_sidebar_nav_li" id="logoutBtn" title="Выйти из системы">
					<div class="general_sidebar_nav_li_icon">
						<img src="images/icons/logout.svg">
					</div>
					<div class="general_sidebar_nav_li_title">Log out</div>
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
					<a href="javascript:void(0)" class="general_add_button" id="addElementBtn" title="Добавить элемент" role="button">
						<img src="images/icons/plus.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="newProjectBtn" title="Новый проект" role="button">
						<img src="images/icons/home.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="loadBtn" title="Загрузить проект" role="button">
						<img src="images/icons/click.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="editBtn" title="Редактировать проект" role="button" style="display: none;">
						<img src="images/icons/text.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="saveBtn" title="Сохранить" role="button">
						<img src="images/icons/save.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="deployBtn" title="Деплой" role="button">
						<img src="images/icons/share.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button" id="exportBtn" title="Export" role="button">
						<img src="images/icons/share.svg">
					</a>
					<a href="javascript:void(0)" class="general_add_button ai-helper-btn" id="aiHelperBtn" title="AI Helper - Создайте сайт с помощью ИИ" role="button">
						<img src="images/icons/stars.svg" class="ai-helper-icon">
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
				<div class="general_sidebar_texter_title">Elements</div>
				<div class="general_sidebar_texter_pretitle">Library</div>
			</div>
			<div class="general_sidebar_library">
				<div class="general_sidebar_element" data-element="group">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/block.svg">
					</div>
					<div class="general_sidebar_element_name">Group</div>
				</div>
				<div class="general_sidebar_element" data-element="text">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/text.svg">
					</div>
					<div class="general_sidebar_element_name">Text</div>
				</div>
				<div class="general_sidebar_element" data-element="button">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/click.svg">
					</div>
					<div class="general_sidebar_element_name">Button</div>
				</div>
				<div class="general_sidebar_element" data-element="image">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/frame.svg">
					</div>
					<div class="general_sidebar_element_name">Image</div>
				</div>
				<div class="general_sidebar_element" data-element="product">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/frame.svg">
					</div>
					<div class="general_sidebar_element_name">Product</div>
				</div>
				<div class="general_sidebar_element" data-element="block">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/block.svg">
					</div>
					<div class="general_sidebar_element_name">Block</div>
				</div>
				<div class="general_sidebar_element" data-element="link">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/link.svg">
					</div>
					<div class="general_sidebar_element_name">Link</div>
				</div>
				<div class="general_sidebar_element" data-element="list">
					<div class="general_sidebar_element_icon">
						<img src="images/icons/list.svg">
					</div>
					<div class="general_sidebar_element_name">List</div>
				</div>
			</div>
			<div class="general_sidebar_element full" data-element="separator">
				<div class="general_sidebar_element_icon">
					<img src="images/icons/sep.svg">
				</div>
				<div class="general_sidebar_element_name">Separator</div>
			</div>
			<!-- Inspector Panel -->
			<div class="inspector" id="inspector" style="display:none;">
				<div class="general_sidebar_texter" style="margin-top: 8px;">
					<div class="general_sidebar_texter_title">Inspector</div>
					<div class="general_sidebar_texter_pretitle">Edit selected element</div>
				</div>
				<div class="inspector_target" id="inspectorTarget"></div>
				<div class="inspector_fields" id="inspectorFields"></div>
				<div class="inspector_actions">
					<button type="button" class="button stroke" id="inspectorDeselect">Deselect</button>
					<button type="button" class="button" id="inspectorRemove">Delete</button>
				</div>
			</div>
		</div>
	</div>
	<div class="sidebar-overlay" id="sidebarOverlay"></div>
	
	<!-- Save Project Modal -->
	<div id="saveModal" class="modal" style="display: none;">
		<div class="modal-content">
			<div class="modal-header">
				<h3>Сохранить проект</h3>
				<button type="button" class="modal-close" id="saveModalClose">&times;</button>
			</div>
			<div class="modal-body">
				<p>Введите название для вашего проекта:</p>
				<input type="text" id="projectName" placeholder="Название проекта" maxlength="100" autofocus>
				<div class="modal-actions">
					<button type="button" class="button stroke" id="saveModalCancel">Отмена</button>
					<button type="button" class="button primary" id="saveModalConfirm">Сохранить</button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Deploy Project Modal -->
	<div id="deployModal" class="modal" style="display: none;">
		<div class="modal-content">
			<div class="modal-header">
				<h3>Деплой проекта</h3>
				<button type="button" class="modal-close" id="deployModalClose">&times;</button>
			</div>
			<div class="modal-body">
				<p>Настройте публичный доступ к вашему проекту:</p>
				
				<div class="form-group">
					<label for="deployProjectName">Название проекта:</label>
					<input type="text" id="deployProjectName" placeholder="Название проекта" maxlength="100">
				</div>
				
				<div class="form-group">
					<label for="deployProjectSlug">URL-адрес:</label>
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
					<label for="deployPrivacy">Приватность:</label>
					<select id="deployPrivacy">
						<option value="public">🌐 Публичный - доступен всем</option>
						<option value="unlisted">🔗 Ссылка - только по прямой ссылке</option>
						<option value="private">🔒 Приватный - только для вас</option>
					</select>
				</div>
				
				<div class="form-group">
					<label for="deployDescription">Описание (необязательно):</label>
					<textarea id="deployDescription" placeholder="Краткое описание вашего проекта..." maxlength="200" rows="3"></textarea>
				</div>
				
				<div class="modal-actions">
					<button type="button" class="button stroke" id="deployModalCancel">Отмена</button>
					<button type="button" class="button primary" id="deployModalConfirm">Деплой</button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Load Project Modal -->
	<div id="loadModal" class="modal" style="display: none;">
		<div class="modal-content">
			<div class="modal-header">
				<h3>Загрузить проект</h3>
				<button type="button" class="modal-close" id="loadModalClose">&times;</button>
			</div>
			<div class="modal-body">
				<p>Выберите проект для продолжения работы:</p>
				<div id="projectsList" class="projects-list">
					<div class="loading">Загрузка проектов...</div>
				</div>
				<div class="modal-actions">
					<button type="button" class="button stroke" id="loadModalCancel">Отмена</button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Edit Project Modal -->
	<div id="editModal" class="modal" style="display: none;">
		<div class="modal-content">
			<div class="modal-header">
				<h3>Редактировать проект</h3>
				<button type="button" class="modal-close" id="editModalClose">&times;</button>
			</div>
			<div class="modal-body">
				<p>Измените настройки вашего проекта:</p>
				
				<div class="form-group">
					<label for="editProjectName">Название проекта:</label>
					<input type="text" id="editProjectName" placeholder="Название проекта" maxlength="100">
				</div>
				
				<div class="form-group">
					<label for="editProjectDescription">Описание (необязательно):</label>
					<textarea id="editProjectDescription" placeholder="Краткое описание вашего проекта..." maxlength="200" rows="3"></textarea>
				</div>
				
				<div class="form-group">
					<label for="editProjectPrivacy">Приватность:</label>
					<select id="editProjectPrivacy">
						<option value="private">🔒 Приватный - только для вас</option>
						<option value="unlisted">🔗 Ссылка - только по прямой ссылке</option>
						<option value="public">🌐 Публичный - доступен всем</option>
					</select>
				</div>
				
				<div class="modal-actions">
					<button type="button" class="button stroke" id="editModalCancel">Отмена</button>
					<button type="button" class="button primary" id="editModalConfirm">Сохранить изменения</button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- AI Helper Modal -->
	<div id="aiHelperModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;">
		<div class="w-full max-w-2xl bg-gradient-to-br from-blue-600 via-purple-600 to-blue-700 rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
			
			<!-- AI Thinking Preloader -->
			<div id="aiThinkingLoader" class="absolute inset-0 bg-gradient-to-br from-blue-600 via-purple-600 to-blue-700 flex items-center justify-center z-10" style="display: none;">
				<div class="text-center">
					<div class="relative mb-6">
						<!-- Animated Brain -->
						<div class="w-20 h-20 mx-auto mb-4 relative thinking-brain">
							<div class="absolute inset-0 bg-white/20 rounded-full animate-pulse"></div>
							<div class="absolute inset-2 bg-white/30 rounded-full animate-ping"></div>
							<div class="absolute inset-4 bg-white/40 rounded-full animate-bounce"></div>
							<svg class="w-12 h-12 absolute inset-4 text-white animate-pulse" fill="currentColor" viewBox="0 0 24 24">
								<path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
							</svg>
						</div>
						
						<!-- Floating Dots -->
						<div class="flex justify-center space-x-2 mb-4">
							<div class="w-3 h-3 bg-white/60 rounded-full thinking-dots" style="animation-delay: 0s;"></div>
							<div class="w-3 h-3 bg-white/60 rounded-full thinking-dots" style="animation-delay: 0.3s;"></div>
							<div class="w-3 h-3 bg-white/60 rounded-full thinking-dots" style="animation-delay: 0.6s;"></div>
						</div>
					</div>
					
					<h3 class="text-2xl font-bold text-white mb-2">🤖 AI думает...</h3>
					<p class="text-white/80 text-sm mb-4" id="thinkingText">Анализирую ваш запрос</p>
					
					<!-- Progress Bar -->
					<div class="w-64 mx-auto bg-white/20 rounded-full h-2 mb-4">
						<div id="thinkingProgress" class="bg-gradient-to-r from-yellow-400 to-orange-500 h-2 rounded-full transition-all duration-1000" style="width: 0%"></div>
					</div>
					
					<button type="button" id="cancelThinking" class="px-4 py-2 bg-white/20 hover:bg-white/30 border border-white/30 text-white rounded-lg text-sm transition-all">
						Отменить
					</button>
				</div>
			</div>
			<!-- Header -->
			<div class="relative p-6 pb-4">
				<div class="flex items-center gap-4">
					<div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-xl">
						<img src="images/icons/stars.svg" class="w-6 h-6 filter brightness-0 invert">
					</div>
					<div class="flex-1">
						<h3 class="text-2xl font-bold text-white">AI Helper</h3>
						<p class="text-white/80 text-sm">Создайте сайт с помощью искусственного интеллекта</p>
					</div>
					<button type="button" id="aiHelperModalClose" class="w-8 h-8 flex items-center justify-center text-white/60 hover:text-white hover:bg-white/10 rounded-lg transition-all">
						<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
						</svg>
					</button>
				</div>
			</div>

			<!-- Body -->
			<div class="px-6 pb-6 space-y-6">
				<!-- Intro -->
				<div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
					<p class="text-white/90 text-sm leading-relaxed">
						Опишите, какой сайт вы хотите создать, и AI автоматически сгенерирует структуру элементов для вас
					</p>
				</div>

				<!-- Description Input -->
				<div class="space-y-2">
					<label for="aiDescription" class="flex items-center gap-2 text-white font-medium">
						<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
						</svg>
						Описание сайта:
					</label>
					<textarea 
						id="aiDescription" 
						rows="4"
						maxlength="1000"
						placeholder="Например: Создай лендинг для интернет-магазина одежды с заголовком, описанием товаров, кнопкой заказа и контактной информацией..."
						class="w-full px-4 py-3 bg-white/15 border border-white/30 rounded-xl text-white placeholder-white/60 backdrop-blur-xl focus:bg-white/20 focus:border-white/50 focus:outline-none transition-all resize-none"
					></textarea>
					<p class="text-white/70 text-xs flex items-center gap-1">
						<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
						</svg>
						Чем подробнее описание, тем лучше результат. Укажите тип сайта, основные разделы, стиль и функциональность.
					</p>
				</div>

				<!-- Style Select -->
				<div class="space-y-2">
					<label for="aiStyle" class="flex items-center gap-2 text-white font-medium">
						<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"/>
						</svg>
						Стиль дизайна:
					</label>
					<select id="aiStyle" class="w-full px-4 py-3 bg-white/15 border border-white/30 rounded-xl text-white backdrop-blur-xl focus:bg-white/20 focus:border-white/50 focus:outline-none transition-all">
						<option value="modern" class="bg-gray-800 text-white">🎨 Современный</option>
						<option value="minimalist" class="bg-gray-800 text-white">⚪ Минималистичный</option>
						<option value="corporate" class="bg-gray-800 text-white">🏢 Корпоративный</option>
						<option value="creative" class="bg-gray-800 text-white">🎭 Креативный</option>
						<option value="elegant" class="bg-gray-800 text-white">✨ Элегантный</option>
					</select>
				</div>

				<!-- Examples -->
				<div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
					<h4 class="text-white font-medium mb-3 flex items-center gap-2">
						<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
						</svg>
						Примеры запросов:
					</h4>
					<div class="flex flex-wrap gap-2">
						<button type="button" onclick="document.getElementById('aiDescription').value = 'Создай лендинг для IT-компании с заголовком о наших услугах, описанием команды, портфолио проектов и формой обратной связи'" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 border border-white/30 text-white text-xs rounded-full transition-all backdrop-blur-xl">
							IT-компания
						</button>
						<button type="button" onclick="document.getElementById('aiDescription').value = 'Создай сайт ресторана с меню, фотографиями блюд, информацией о шеф-поваре и формой бронирования столика'" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 border border-white/30 text-white text-xs rounded-full transition-all backdrop-blur-xl">
							Ресторан
						</button>
						<button type="button" onclick="document.getElementById('aiDescription').value = 'Создай портфолио фотографа с галереей работ, информацией обо мне, услугами и контактами'" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 border border-white/30 text-white text-xs rounded-full transition-all backdrop-blur-xl">
							Портфолио
						</button>
					</div>
				</div>

				<!-- Actions -->
				<div class="flex gap-3 pt-2">
					<button type="button" id="aiHelperModalCancel" class="flex-1 px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/30 text-white rounded-xl font-medium transition-all backdrop-blur-xl">
						Отмена
					</button>
					<button type="button" id="aiHelperModalGenerate" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-xl font-medium transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 flex items-center justify-center gap-2">
						<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
						</svg>
						Сгенерировать с AI
					</button>
				</div>
			</div>
		</div>
	</div>
	
	<style>
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
	</script>
</body>
</html>