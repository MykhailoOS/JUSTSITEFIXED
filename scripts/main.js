// Global functions for save functionality
async function saveProject(projectTitle = 'Мой проект') {
	try {
		const canvas = serializeCanvasOuterHtml();
		
		// Debug logging
		console.log('Saving project with canvas length:', canvas.length);
		console.log('Canvas preview:', canvas.substring(0, 300) + '...');
		
		// Count elements in canvas
		const elementCount = (canvas.match(/class="el_/g) || []).length;
		console.log('Number of elements being saved:', elementCount);
		
		const form = new FormData();
		form.append('title', projectTitle);
		form.append('canvas', canvas);
		
		// Include current project ID if available
		const currentProjectId = getCurrentProjectId();
		if (currentProjectId) {
			form.append('id', currentProjectId);
			console.log('Updating existing project ID:', currentProjectId);
		} else {
			console.log('Creating new project');
		}
		
		// Use absolute path from document base to avoid /index.php/api/ resolution
		const apiUrl = new URL('api/project_save.php', document.baseURI).pathname;
		const res = await fetch(apiUrl, {
			method: 'POST',
			credentials: 'include',
			body: form
		});
		if (!res.ok) {
			if (res.status === 401) { window.location.href = 'login.php'; return; }
			const txt = await res.text();
			alert('Ошибка сохранения (HTTP ' + res.status + '): ' + txt);
			return;
		}
		const data = await res.json();
		if (data.ok) { 
			// Save project state to localStorage
			saveProjectState(data.id, projectTitle, canvas);
			
			// Show success notification
			showNotification('Проект "' + projectTitle + '" успешно сохранен!', 'success');
		}
		else { 
			showNotification('Ошибка сохранения: ' + (data.error || 'unknown'), 'error');
		}
	} catch (e) {
		showNotification('Ошибка сети: ' + e.message, 'error');
	}
}

function serializeCanvasOuterHtml() {
	const $canvas = $('.general_canva').clone(true, true);
	
	// Debug: log original canvas state
	console.log('Original canvas children count:', $('.general_canva').children().length);
	console.log('Cloned canvas children count:', $canvas.children().length);
	
	// Clean up temporary classes
	$canvas.find('.element-adding').removeClass('element-adding');
	$canvas.find('.highlight-element').removeClass('highlight-element');
	$canvas.find('.edit_mode').removeClass('edit_mode');
	
	const serialized = $canvas.prop('outerHTML');
	
	// Debug: log serialized result
	console.log('Serialized canvas length:', serialized.length);
	console.log('Elements in serialized canvas:', (serialized.match(/class="el_/g) || []).length);
	
	return serialized;
}

function showNotification(message, type = 'info') {
	// Remove existing notifications
	$('.notification').remove();
	
	const notification = $(`
		<div class="notification notification-${type}" style="
			position: fixed;
			top: 20px;
			right: 20px;
			background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#F44336' : '#2196F3'};
			color: white;
			padding: 12px 20px;
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0,0,0,0.3);
			z-index: 10001;
			font-size: 14px;
			font-weight: 500;
			max-width: 300px;
			word-wrap: break-word;
		">
			${message}
		</div>
	`);
	
	$('body').append(notification);
	
	// Auto remove after 4 seconds
	setTimeout(() => {
		notification.fadeOut(300, function() {
			$(this).remove();
		});
	}, 4000);
}

// Project state management functions
function saveProjectState(projectId, title, canvas) {
	const projectState = {
		id: projectId,
		title: title,
		canvas: canvas,
		lastSaved: Date.now(),
		hasUnsavedChanges: false
	};
	localStorage.setItem('currentProject', JSON.stringify(projectState));
	console.log('Project state saved:', projectState);
}

function getCurrentProjectId() {
	const projectState = getProjectState();
	return projectState ? projectState.id : null;
}

function getProjectState() {
	try {
		const state = localStorage.getItem('currentProject');
		return state ? JSON.parse(state) : null;
	} catch (e) {
		console.error('Error reading project state:', e);
		return null;
	}
}

function clearProjectState() {
	localStorage.removeItem('currentProject');
	console.log('Project state cleared');
}

function markProjectAsChanged() {
	const projectState = getProjectState();
	if (projectState) {
		projectState.hasUnsavedChanges = true;
		projectState.lastModified = Date.now();
		localStorage.setItem('currentProject', JSON.stringify(projectState));
	}
}

function markProjectAsSaved() {
	const projectState = getProjectState();
	if (projectState) {
		projectState.hasUnsavedChanges = false;
		projectState.lastSaved = Date.now();
		localStorage.setItem('currentProject', JSON.stringify(projectState));
	}
}

// Auto-save functionality
let autoSaveTimeout = null;
function scheduleAutoSave() {
	if (autoSaveTimeout) {
		clearTimeout(autoSaveTimeout);
	}
	
	autoSaveTimeout = setTimeout(async () => {
		const projectState = getProjectState();
		if (projectState && projectState.hasUnsavedChanges && projectState.id) {
			try {
				console.log('Auto-saving project...');
				await autoSaveProject();
			} catch (e) {
				console.error('Auto-save failed:', e);
			}
		}
	}, 5000); // Auto-save after 5 seconds of inactivity
}

async function autoSaveProject() {
	const projectState = getProjectState();
	if (!projectState || !projectState.id) return;
	
	try {
		const canvas = serializeCanvasOuterHtml();
		const form = new FormData();
		form.append('id', projectState.id);
		form.append('title', projectState.title);
		form.append('canvas', canvas);
		
		const apiUrl = new URL('api/project_save.php', document.baseURI).pathname;
		const res = await fetch(apiUrl, {
			method: 'POST',
			credentials: 'include',
			body: form
		});
		
		if (res.ok) {
			const data = await res.json();
			if (data.ok) {
				markProjectAsSaved();
				console.log('Project auto-saved successfully');
			}
		}
	} catch (e) {
		console.error('Auto-save error:', e);
	}
}

// Initialize project state on page load
function initializeProjectState() {
	const projectState = getProjectState();
	if (projectState && projectState.id) {
		console.log('Restoring project state:', projectState);
		
		// Restore canvas content if it exists and is different from current
		const currentCanvas = $('.general_canva').html();
		if (projectState.canvas && projectState.canvas !== currentCanvas) {
			$('.general_canva').html(projectState.canvas);
			console.log('Canvas content restored from localStorage');
			
			// Reinitialize draggable elements after content is restored
			setTimeout(() => {
				if (window.initDraggable) {
					window.initDraggable();
				}
			}, 100);
		}
		
		// Update page title
		if (projectState.title) {
			document.title = `${projectState.title} — ${document.title.split(' — ')[1] || 'Builder'}`;
		}
		
		// Update UI state
		document.getElementById('saveBtn').style.display = 'none';
		document.getElementById('editBtn').style.display = 'block';
		
		// Show unsaved changes indicator if needed
		if (projectState.hasUnsavedChanges) {
			showNotification('У вас есть несохраненные изменения', 'info');
		}
	}
}

$(document).ready(function() {
	let elementCounter = 1;
	// Make elementCounter globally accessible
	window.elementCounter = elementCounter;
	let sidebarIsOpen = false;
	let $selectedBlock = null;
	
	// Initialize project state management after DOM is ready
	setTimeout(() => {
		initializeProjectState();
	}, 100);
	
	// Track canvas changes for auto-save
	$(document).on('DOMSubtreeModified', '.general_canva', function() {
		markProjectAsChanged();
		scheduleAutoSave();
		
		// Also save current state to localStorage immediately
		const projectState = getProjectState();
		if (projectState && projectState.id) {
			const currentCanvas = serializeCanvasOuterHtml();
			projectState.canvas = currentCanvas;
			projectState.lastModified = Date.now();
			localStorage.setItem('currentProject', JSON.stringify(projectState));
		}
	});
	
	// Track inspector changes
	$(document).on('input change', '#inspectorFields input, #inspectorFields textarea, #inspectorFields select', function() {
		markProjectAsChanged();
		scheduleAutoSave();
		
		// Also save current state to localStorage immediately
		const projectState = getProjectState();
		if (projectState && projectState.id) {
			const currentCanvas = serializeCanvasOuterHtml();
			projectState.canvas = currentCanvas;
			projectState.lastModified = Date.now();
			localStorage.setItem('currentProject', JSON.stringify(projectState));
		}
	});

	// Функции подсветки элементов на холсте
	function highlightCanvasElements() {
		$('.general_canva').find('[class*="el_"]').each(function(index) {
			const $element = $(this);
			setTimeout(() => {
				$element.addClass('highlight-element');
			}, index * 50);
		});
	}

	function removeCanvasHighlight() {
		$('.general_canva').find('[class*="el_"]').removeClass('highlight-element');
	}

	// Функции создания элементов
	window.createElement = function(type, options = {}) {
		elementCounter++;
		window.elementCounter = elementCounter; // Sync global counter
		let elementHtml = '';
		const content = options.content || '';
		const style = options.style || 'default';
		
		// Apply AI-generated content and styling
		const getTextContent = () => {
			if (content) return content;
			switch(style) {
				case 'header': return 'Заголовок';
				case 'subtitle': return 'Подзаголовок';
				case 'description': return 'Описание';
				default: return 'Текст';
			}
		};
		
		const getButtonContent = () => {
			if (content) return content;
			switch(style) {
				case 'button-primary': return 'Основная кнопка';
				case 'button-secondary': return 'Дополнительная кнопка';
				default: return 'Кнопка';
			}
		};
		
		const getTextStyle = () => {
			switch(style) {
				case 'header': return 'font-size: 32px; font-weight: 700; color: var(--corp);';
				case 'subtitle': return 'font-size: 24px; font-weight: 600; color: var(--dark_gray);';
				case 'description': return 'font-size: 18px; color: var(--dark_gray); line-height: 1.6;';
				default: return '';
			}
		};
		
		const getButtonStyle = () => {
			switch(style) {
				case 'button-primary': return 'background-color: var(--accent); color: white;';
				case 'button-secondary': return 'background-color: transparent; color: var(--corp); border: 2px solid var(--corp);';
				default: return '';
			}
		};
		
		switch(type) {
			case 'group':
				elementHtml = `
					<div class="el_group_block element-adding" data-id="${elementCounter}">
						<div class="el_group" style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start;justify-content:flex-start;padding:16px;background-color:transparent;border-radius:12px;border:2px dashed var(--gray);"></div>
					</div>
				`;
				break;
			case 'product':
				// Compose product from existing element types (groups, image, texts, buttons)
				const idBase = elementCounter;
				elementHtml = `
					<div class="el_product_block element-adding" data-id="${idBase}">
						<div class="el_product">
							<div class="el_product_image" data-id="${idBase}-img" style="width:200px;height:200px;background-color:#E9E9ED;border-radius:8px;background-size:cover;background-position:center;background-repeat:no-repeat;">
								<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#999;font-size:14px;">Image</div>
							</div>
							<div class="el_product_content">
								<div class="el_product_title" data-id="${idBase}-title" style="font-weight:700;font-size:21px;color:var(--corp);">${content || 'Product title'}</div>
								<div class="el_product_desc" data-id="${idBase}-desc" style="color:var(--dark_gray);font-size:16px;">Short description of the product goes here.</div>
								<div class="el_product_price" data-id="${idBase}-price" style="color:var(--accent);font-weight:700;font-size:21px;">$49</div>
								<div class="el_product_actions" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;">
									<a href="#" class="el_button" data-id="${idBase}-buy" style="padding:8px 16px;background-color:var(--accent);color:white;text-decoration:none;border-radius:4px;font-size:14px;">Buy</a>
									<a href="#" class="el_button" data-id="${idBase}-details" style="padding:8px 16px;background-color:transparent;color:var(--corp);text-decoration:none;border:1px solid var(--corp);border-radius:4px;font-size:14px;">Details</a>
								</div>
							</div>
						</div>
					</div>
				`;
				break;
			case 'text':
				elementHtml = `
					<div class="el_text_block element-adding" data-id="${elementCounter}">
						<div class="el_text" style="${getTextStyle()}">${getTextContent()}</div>
					</div>
				`;
				break;
			case 'button':
				elementHtml = `
					<div class="el_button_block element-adding" data-id="${elementCounter}">
						<a href="#" class="el_button" style="${getButtonStyle()}">${getButtonContent()}</a>
					</div>
				`;
				break;
			case 'image':
				elementHtml = `
					<div class="el_image_block element-adding" data-id="${elementCounter}">
						<div class="el_image" style="width:200px; height:200px; background-color:#f5f5f7; border-radius:8px; display:flex; align-items:center; justify-content:center;">
							<div style="text-align:center; color:#999; font-size:14px;">Image</div>
						</div>
					</div>
				`;
				break;
			case 'block':
				elementHtml = `
					<div class="el_block_container element-adding" data-id="${elementCounter}">
						<div class="el_block">Empty Block</div>
					</div>
				`;
				break;
			case 'link':
				elementHtml = `
					<div class="el_link_block element-adding" data-id="${elementCounter}">
						<a href="#" class="el_link">${content || 'Sample Link'}</a>
					</div>
				`;
				break;
			case 'list':
				elementHtml = `
					<div class="el_list_block element-adding" data-id="${elementCounter}">
						<ul class="el_list">
							<li>List item 1</li>
							<li>List item 2</li>
							<li>List item 3</li>
						</ul>
					</div>
				`;
				break;
			case 'separator':
				elementHtml = `
					<div class="el_separator_block element-adding" data-id="${elementCounter}">
						<div class="el_separator"></div>
					</div>
				`;
				break;
			case 'product-card':
				elementHtml = `
					<div class="el_product_card_block element-adding" data-id="${elementCounter}">
						<div class="el_product_card" style="width: 300px; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; transition: all 0.3s ease;">
							<div class="product_image_container" style="position: relative; width: 100%; height: 200px; background: #f5f5f7; display: flex; align-items: center; justify-content: center; cursor: pointer;">
								<div class="product_image_placeholder" style="color: #999; font-size: 14px; text-align: center;">
									<div style="font-size: 24px; margin-bottom: 8px;">📷</div>
									<div>Нажмите для загрузки</div>
								</div>
								<input type="file" class="product_image_input" accept="image/*" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
							</div>
							<div class="product_content" style="padding: 16px;">
								<div class="product_badge" style="display: inline-block; background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-bottom: 8px;">NEW</div>
								<h3 class="product_title" style="margin: 0 0 8px 0; font-size: 18px; font-weight: 600; color: #1a1a1a;">Название товара</h3>
								<p class="product_description" style="margin: 0 0 12px 0; font-size: 14px; color: #666; line-height: 1.4;">Краткое описание товара, которое привлекает внимание покупателей</p>
								<div class="product_price_container" style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
									<span class="product_price" style="font-size: 20px; font-weight: 700; color: #1976d2;">$99</span>
									<span class="product_old_price" style="font-size: 14px; color: #999; text-decoration: line-through;">$129</span>
									<span class="product_discount" style="background: #ff4444; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; font-weight: 600;">-23%</span>
								</div>
								<div class="product_actions" style="display: flex; gap: 8px;">
									<button class="product_buy_btn" style="flex: 1; background: #1976d2; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">Купить</button>
									<button class="product_cart_btn" style="background: #f5f5f5; color: #666; border: none; padding: 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;">🛒</button>
									<button class="product_like_btn" style="background: #f5f5f5; color: #666; border: none; padding: 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;">❤️</button>
								</div>
							</div>
						</div>
					</div>
				`;
				break;
		}
		return elementHtml;
	}

	window.addElementToCanvas = function(type, $targetGroup, options = {}) {
		console.log('Adding element to canvas:', type, options);
		console.log('Target group:', $targetGroup);
		console.log('Selected block:', $selectedBlock);
		
		const elementHtml = createElement(type, options);
		const $newElement = $(elementHtml);
		$newElement.attr('draggable', true).addClass('draggable');
		
		console.log('Created element HTML:', elementHtml);
		console.log('Canvas elements before append:', $('.general_canva').children().length);
		
		// Добавляем элемент в правильное место
		if ($targetGroup && $targetGroup.length) {
			// Если указана целевая группа, добавляем в неё
			console.log('Adding to target group');
			$targetGroup.append($newElement);
		} else if ($selectedBlock && $selectedBlock.hasClass('el_group_block')) {
			// Если выбран блок группы, добавляем внутрь группы
			console.log('Adding to selected group block');
			$selectedBlock.find('.el_group').first().append($newElement);
		} else if ($selectedBlock && $selectedBlock.closest('.el_group').length) {
			// Если выбранный элемент находится внутри группы, добавляем в эту группу
			console.log('Adding to closest group');
			$selectedBlock.closest('.el_group').append($newElement);
		} else {
			// Проверяем, есть ли активная группа на холсте
			const $activeGroup = $('.general_canva .el_group_block.edit_mode');
			if ($activeGroup.length) {
				// Если есть активная группа, добавляем в неё
				console.log('Adding to active group');
				$activeGroup.find('.el_group').first().append($newElement);
			} else {
				// Иначе просто добавляем в конец холста
				console.log('Adding to canvas end');
				$('.general_canva').append($newElement);
			}
		}
		
		console.log('Canvas elements after append:', $('.general_canva').children().length);
		
		// Убираем класс анимации после завершения
		setTimeout(() => { 
			$newElement.removeClass('element-adding'); 
		}, 500);
		
		// Делаем новый элемент перетаскиваемым
		$newElement.attr('draggable', true).addClass('draggable');
		
		// Инициализируем перетаскивание для нового элемента
		if (window.initDraggable) {
			window.initDraggable();
		}
		
		// Закрываем сайдбар после добавления (только если не AI генерация)
		if (!options.aiGenerated) {
			setTimeout(() => { 
				closeSidebar(); 
			}, 300);
		}
	}

	// Атрибуты data-element уже добавлены в PHP, проверяем их наличие
	console.log('Elements with data-element:', $('.general_sidebar_element[data-element]').length);

	// Обработчик клика на элементы в библиотеке
	$(document).on('click', '.general_sidebar_element', function(e) {
		e.preventDefault();
		const elementType = $(this).data('element');
		console.log('Clicked element type:', elementType);
		console.log('Current canvas elements before add:', $('.general_canva').children().length);
		
		if (elementType) {
			// Добавляем элемент без указания целевой группы
			addElementToCanvas(elementType, null, {});
			$(this).css({ 'transform': 'scale(0.95)', 'transition': 'transform 0.1s ease-out' });
			setTimeout(() => { $(this).css('transform', 'scale(1)'); }, 100);
		}
	});

	// ---- Drag & Drop ----
	let $dragging = null;
	
	// Make initDraggable globally available
	window.initDraggable = function() {
		const sel = '.el_text_block, .el_button_block, .el_image_block, .el_block_container, .el_link_block, .el_list_block, .el_separator_block, .el_group_block, .el_product_block, .el_product_card_block';
		console.log('Initializing draggable for elements:', $('.general_canva').find(sel).length);
		$('.general_canva').find(sel).attr('draggable', true).addClass('draggable');
		console.log('After init draggable, canvas elements:', $('.general_canva').children().length);
	};
	
	// Инициализируем перетаскивание только один раз
	window.initDraggable();

	$(document).on('dragstart', '.general_canva .draggable', function(e){
		$dragging = $(this);
		$(this).addClass('dragging');
		e.originalEvent.dataTransfer.effectAllowed = 'move';
		e.originalEvent.dataTransfer.setData('text/plain', 'dnd');
	});

	$(document).on('dragend', '.general_canva .draggable', function(){
		$(this).removeClass('dragging');
		$('.drop-target').removeClass('drop-target');
		$('.drop-indicator').remove(); // Remove drop indicators
		$dragging = null;
	});

	// Allow drop on groups and canvas (target normalization)
	$(document).on('dragover', '.general_canva, .el_group, .general_canva *', function(e){
		const $t = $(e.target).closest('.el_group, .general_canva');
		if ($t.length === 0) return;
		e.preventDefault();
		$t.addClass('drop-target');
		e.originalEvent.dataTransfer.dropEffect = 'move';
		
		// Show insertion indicator
		if ($dragging && $dragging.length) {
			$('.drop-indicator').remove(); // Remove existing indicators
			
			const dropY = e.originalEvent.clientY;
			let $insertBefore = null;
			
			const $children = $t.hasClass('el_group') ? 
				$t.children().not($dragging) : 
				$('.general_canva').children().not($dragging);
				
			$children.each(function() {
				const rect = this.getBoundingClientRect();
				const elementMiddle = rect.top + rect.height / 2;
				if (dropY < elementMiddle) {
					$insertBefore = $(this);
					return false; // break the loop
				}
			});
			
			// Create drop indicator
			const $indicator = $('<div class="drop-indicator" style="height: 2px; background: #667eea; margin: 2px 0; border-radius: 1px; opacity: 0.8;"></div>');
			
			if ($insertBefore) {
				$insertBefore.before($indicator);
			} else {
				if ($t.hasClass('el_group')) {
					$t.append($indicator);
				} else {
					$('.general_canva').append($indicator);
				}
			}
		}
	});

	$(document).on('dragleave', '.general_canva, .el_group, .general_canva *', function(e){
		const $t = $(e.target).closest('.el_group, .general_canva');
		if ($t.length) $t.removeClass('drop-target');
	});

	$(document).on('drop', '.general_canva, .el_group, .general_canva *', function(e){
		const $t = $(e.target).closest('.el_group, .general_canva');
		if ($t.length === 0) return;
		e.preventDefault();
		if ($dragging && $dragging.length) {
			// Find the correct insertion point based on mouse position
			const dropY = e.originalEvent.clientY;
			let $insertBefore = null;
			
			if ($t.hasClass('el_group')) {
				// Dropping into a group
				const $children = $t.children().not($dragging);
				$children.each(function() {
					const rect = this.getBoundingClientRect();
					const elementMiddle = rect.top + rect.height / 2;
					if (dropY < elementMiddle) {
						$insertBefore = $(this);
						return false; // break the loop
					}
				});
				
				if ($insertBefore) {
					$insertBefore.before($dragging);
				} else {
					$t.append($dragging);
				}
			} else {
				// Dropping into canvas
				const $children = $('.general_canva').children().not($dragging);
				$children.each(function() {
					const rect = this.getBoundingClientRect();
					const elementMiddle = rect.top + rect.height / 2;
					if (dropY < elementMiddle) {
						$insertBefore = $(this);
						return false; // break the loop
					}
				});
				
				if ($insertBefore) {
					$insertBefore.before($dragging);
				} else {
					$('.general_canva').append($dragging);
				}
			}
			
			// Save state after drag & drop
			const projectState = getProjectState();
			if (projectState && projectState.id) {
				const currentCanvas = serializeCanvasOuterHtml();
				projectState.canvas = currentCanvas;
				projectState.lastModified = Date.now();
				localStorage.setItem('currentProject', JSON.stringify(projectState));
				markProjectAsChanged();
			}
		}
		$('.drop-indicator').remove(); // Remove drop indicators
		$t.removeClass('drop-target');
	});

	// Обработчик кнопки добавления элементов
	$('.general_add_button').on('click', function(e) {
		// Do not interfere with specific buttons
		if ($(this).is('#saveBtn, #loadBtn, #editBtn, #deployBtn, #exportBtn, #newProjectBtn')) { 
			return; 
		}
		e.preventDefault();
		// Открываем сайдбар для всех кнопок с иконкой plus.svg (кнопка добавления элемента)
		if ($(this).find('img').attr('src').includes('plus.svg')) {
			toggleSidebar();
			animateButton($(this));
		}
		return false;
	});

    // Функция переключения сайдбара
	function toggleSidebar() {
		if (sidebarIsOpen) { closeSidebar(); } else { openSidebar(); }
	}

    // ----- Mobile-only editing helpers -----
    const isMobileMode = () => $('.general_group').hasClass('mobile');

    function setStyleForMode($el, cssProp, cssValue) {
        if (cssValue === undefined) return;
        const prop = String(cssProp);
        if (isMobileMode()) {
            const desktopAttr = `data-desktop-${prop}`;
            if (typeof $el.attr(desktopAttr) === 'undefined') {
                const current = $el.css(prop) || '';
                $el.attr(desktopAttr, current);
            }
            $el.attr(`data-mobile-${prop}`, cssValue);
        }
        $el.css(prop, cssValue);
    }

    function applyOverrides(mode) {
        const all = document.querySelectorAll('*');
        all.forEach(el => {
            const $el = $(el);
            for (let i = 0; i < el.attributes.length; i++) {
                const a = el.attributes[i];
                if (mode === 'mobile' && a.name.startsWith('data-mobile-')) {
                    const prop = a.name.replace('data-mobile-', '');
                    $el.css(prop, a.value);
                } else if (mode === 'desktop' && a.name.startsWith('data-desktop-')) {
                    const prop = a.name.replace('data-desktop-', '');
                    $el.css(prop, a.value || '');
                }
            }
        });
    }

    // View mode toggle with override application
    $(document).on('click', '.general_view_desktop', function(){
        $('.general_view').removeClass('active');
        $(this).addClass('active');
        $('.general_group').removeClass('mobile');
        applyOverrides('desktop');
    });
    $(document).on('click', '.general_view_mobile', function(){
        $('.general_view').removeClass('active');
        $(this).addClass('active');
        $('.general_group').addClass('mobile');
        applyOverrides('mobile');
    });

	// ===== Export project =====
	$('#exportBtn').on('click', function(e){
		e.preventDefault();
		exportProject();
	});

	// ===== Save project (requires login) =====
	// Save button handler moved to index.php to show modal first

	function exportProject() {
		const $canvas = $('.general_canva').clone(true, true);
		$canvas.find('.element-adding').removeClass('element-adding');
		$canvas.find('.highlight-element').removeClass('highlight-element');
		$canvas.find('.edit_mode').removeClass('edit_mode');
		const title = $('title').text() || 'JustSite Export';
		const cssLinks = Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map(l=>l.href);
		const fontLinks = Array.from(document.querySelectorAll('link[rel="stylesheet"][href*="fonts.googleapis.com"]')).map(l=>l.href);
		const links = [...new Set([...cssLinks, ...fontLinks])];
		const html = `<!DOCTYPE html>\n<html lang="en">\n<head>\n<meta charset="UTF-8">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>${title}</title>\n${links.map(h=>`<link rel=\"stylesheet\" href=\"${h}\">`).join('')}\n</head>\n<body style=\"margin:0;\">\n<div class=\"export_container\" style=\"padding:24px;\">\n${$canvas.prop('outerHTML')}\n</div>\n</body>\n</html>`;
		const blob = new Blob([html], { type: 'text/html;charset=utf-8' });
		const url = URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = 'justsite-export.html';
		document.body.appendChild(a);
		a.click();
		setTimeout(()=>{ URL.revokeObjectURL(url); a.remove(); }, 1000);
	}


	function openSidebar(mode = 'library') {
		const isMobile = window.matchMedia('(max-width: 768px)').matches;
		$('.general_sidebar_right').css({ 'transform': isMobile ? 'translateY(0)' : 'translateX(0)', 'transition': 'transform 0.3s ease-out' });
		$('.general_sidebar_right').addClass('sidebar-open');
		$('#sidebarOverlay').addClass('active');
		sidebarIsOpen = true;
		if (mode === 'inspector') {
			$('.general_sidebar_right').addClass('inspector-open');
			$('#inspector').show();
			$('body').css('overflow', 'hidden');
		} else {
			$('.general_sidebar_right').removeClass('inspector-open');
			$('#inspector').hide();
			$('body').css('overflow', 'hidden');
			highlightCanvasElements();
			setTimeout(() => { animateSidebarElements(); }, 100);
		}
	}

	function closeSidebar() {
		const isMobile = window.matchMedia('(max-width: 768px)').matches;
		$('.general_sidebar_right').css({ 'transform': isMobile ? 'translateY(100%)' : 'translateX(100%)', 'transition': 'transform 0.3s ease-out' });
		$('.general_sidebar_right').removeClass('sidebar-open');
		$('.general_sidebar_right').removeClass('inspector-open');
		$('#inspector').hide();
		$('#sidebarOverlay').removeClass('active');
		sidebarIsOpen = false;
		$('body').css('overflow', 'auto');
		removeCanvasHighlight();
	}

	function animateSidebarElements() {
		$('.general_sidebar_element').each(function(index) {
			$(this).css({ 'opacity': '0', 'transform': 'translateY(20px)', 'transition': 'opacity 0.3s ease-out, transform 0.3s ease-out' });
			setTimeout(() => { $(this).css({ 'opacity': '1', 'transform': 'translateY(0px)' }); }, 150 + (index * 100));
		});
	}

	function animateButton($button) {
		$button.css({ 'transform': 'rotate(45deg)', 'transition': 'transform 0.3s ease-out' });
		setTimeout(function() { $button.css('transform', 'rotate(0deg)'); }, 300);
		createRippleEffect($button);
	}

	function createRippleEffect($button) {
		const $ripple = $('<span class="ripple"></span>');
		$button.append($ripple);
		setTimeout(function() { $ripple.remove(); }, 600);
	}

	// Закрытие сайдбара при клике вне его
	$(document).on('click', function(e) {
		if (sidebarIsOpen && !$(e.target).closest('.general_sidebar_right').length && !$(e.target).closest('.general_add_button').length) {
			closeSidebar();
		}
	});

	// Кнопка закрытия и оверлей
	$(document).on('click', '#sidebarClose', function(e){ e.preventDefault(); closeSidebar(); });
	$('#sidebarOverlay').on('click', function(){ closeSidebar(); });

	// Enhanced touch events for mobile
	$(document).on('touchstart', '.general_sidebar_element', function(e) {
		e.preventDefault();
		$(this).addClass('touching');
	});
	
	$(document).on('touchend', '.general_sidebar_element', function(e) {
		e.preventDefault();
		$(this).removeClass('touching');
		const elementType = $(this).data('element');
		if (elementType) { 
			addElementToCanvas(elementType, null, {}); 
		}
	});
	
	// Enhanced mobile sidebar navigation
	$(document).on('click touchend', '.general_sidebar_nav_li', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		// Add visual feedback
		$(this).css('background', 'rgba(255, 255, 255, 0.2)');
		setTimeout(() => {
			$(this).css('background', '');
		}, 200);
		
		// Handle navigation based on content
		const title = $(this).find('.general_sidebar_nav_li_title').text().trim();
		
		if (title === 'Home') {
			// Scroll to top or refresh canvas view
			$('.general_canva')[0].scrollIntoView({ behavior: 'smooth' });
		}
		// Add more navigation handlers as needed
	});
	
	// Improve mobile touch targets (will be moved to main ready block)

	// Закрытие сайдбара по Escape
	$(document).on('keydown', function(e) { if (e.key === 'Escape' && sidebarIsOpen) { closeSidebar(); } });

	// Ховер эффекты
	$('.general_sidebar_element').hover(
		function() { $(this).css({ 'transform': 'scale(1.05)', 'transition': 'transform 0.2s ease-out' }); },
		function() { $(this).css('transform', 'scale(1)'); }
	);

	$('.general_add_button').hover(
		function() { $(this).css({ 'transform': 'scale(1.1)', 'box-shadow': '0 4px 12px rgba(26, 28, 54, 0.3)', 'transition': 'all 0.3s ease-out' }); },
		function() { if (!$(this).is(':active')) { $(this).css({ 'transform': 'scale(1)', 'box-shadow': 'none' }); } }
	);

	$('.general_add_button').on('mousedown', function() { $(this).css('transform', 'scale(0.95)'); });
	$('.general_add_button').on('mouseup', function() { $(this).css('transform', 'scale(1.1)'); });

	// Стилизация скроллбара для сайдбара
	$('.general_sidebar_right').css({ 'overflow-y': 'auto', 'scrollbar-width': 'thin', 'scrollbar-color': '#ccc transparent' });

	// Плавные переходы для элементов
	$('.general_sidebar_element').css({ 'transition': 'all 0.3s ease-out' });
	$('.general_sidebar_texter_title, .general_sidebar_texter_pretitle').css({ 'transition': 'opacity 0.5s ease-out, transform 0.5s ease-out' });

	// Инициализация: скрываем сайдбар по умолчанию
	$('.general_sidebar_right').css({ 'transform': 'translateX(100%)', 'transition': 'transform 0.3s ease-out' });

	// ============= Inspector Logic =============
	function getBlockMeta($block) {
		if (!$block || $block.length === 0) return { type: 'unknown', title: 'Unknown' };
		if ($block.hasClass('el_text_block')) return { type: 'text', title: 'Text' };
		if ($block.hasClass('el_button_block')) return { type: 'button', title: 'Button' };
		if ($block.hasClass('el_image_block')) return { type: 'image', title: 'Image' };
		if ($block.hasClass('el_block_container')) return { type: 'block', title: 'Block' };
		if ($block.hasClass('el_group_block')) return { type: 'group', title: 'Group' };
		if ($block.hasClass('el_product_block')) return { type: 'product', title: 'Product' };
		if ($block.hasClass('el_product_card_block')) return { type: 'product-card', title: 'Product Card' };
		if ($block.hasClass('el_link_block')) return { type: 'link', title: 'Link' };
		if ($block.hasClass('el_list_block')) return { type: 'list', title: 'List' };
		if ($block.hasClass('el_separator_block')) return { type: 'separator', title: 'Separator' };
		if ($block.hasClass('general_canva')) return { type: 'canvas', title: 'Canvas' };
		return { type: 'unknown', title: 'Unknown' };
	}

	function buildField({ id, label, type = 'text', value = '', min, max, step, placeholder, options }) {
		const inputAttrs = [
			`id="${id}"`,
			`data-field="${id}"`,
			`class="input"`,
			type ? `type="${type}"` : '',
			value !== undefined ? `value="${value}"` : '',
			min !== undefined ? `min="${min}"` : '',
			max !== undefined ? `max="${max}"` : '',
			step !== undefined ? `step="${step}"` : '',
			placeholder ? `placeholder="${placeholder}"` : ''
		].filter(Boolean).join(' ');
		if (type === 'select') {
			const opts = (options || []).map(o => `<option value="${o.value}" ${String(o.value)===String(value)?'selected':''}>${o.label}</option>`).join('');
			return (`<div class="inspector_field"><label for="${id}" class="inspector_label">${label}</label><select id="${id}" data-field="${id}" class="input">${opts}</select></div>`);
		}
		return (`<div class="inspector_field"><label for="${id}" class="inspector_label">${label}</label><input ${inputAttrs} /></div>`);
	}

	function buildSizeFields(prefix, label, cssValue) {
		const { num, unit } = parseCssSize(cssValue);
		return (
			`<div class="inspector_field"><label class="inspector_label">${label}</label><div class="inspector_grid_2">${buildField({ id: `${prefix}Value`, label: 'Value', type: 'number', value: num, min: 0 })}${buildField({ id: `${prefix}Unit`, label: 'Unit', type: 'select', value: unit, options:[{value:'px',label:'px'},{value:'%',label:'%'}] })}</div></div>`
		);
	}

	function parseCssSize(v) {
		if (!v) return { num: 0, unit: 'px' };
		const m = String(v).trim().match(/([\d\.]+)\s*(px|%)/);
		if (m) return { num: parseFloat(m[1]), unit: m[2] };
		if (String(v).includes('%')) return { num: parseFloat(v) || 0, unit: '%' };
		return { num: parseInt(v) || 0, unit: 'px' };
	}

	function getSizeFromFields(prefix) {
		const num = $(`#${prefix}Value`).val();
		const unit = $(`#${prefix}Unit`).val();
		if (num === undefined || unit === undefined) return '';
		if (num === '' || isNaN(Number(num))) return '';
		return `${num}${unit}`;
	}

	function buildTextarea({ id, label, value = '', placeholder }) {
		return (`<div class="inspector_field"><label for="${id}" class="inspector_label">${label}</label><textarea id="${id}" data-field="${id}" class="textarea" placeholder="${placeholder || ''}">${value}</textarea></div>`);
	}

	function showInspectorFor($block) {
		const meta = getBlockMeta($block);
		$('.general_sidebar_right').addClass('inspector-open');
		$('#inspector').show();
		$('#inspectorTarget').html(`<div class="inspector_target_badge">${meta.title}</div>`);
		const $fields = $('#inspectorFields');
		$fields.empty();
		if (meta.type === 'text') {
			const text = $block.find('.el_text').text();
			const currentFont = $block.find('.el_text').css('font-family').replace(/['"]/g, '').split(',')[0].trim();
			$fields.append(`<div class="inspector_field"><div class="inspector_label">Text</div><div style="padding: 8px; background: #f0f8ff; border: 1px solid #007bff; border-radius: 4px; font-size: 13px; color: #0056b3;">💡 Двойной клик для редактирования</div></div>`);
			$fields.append(buildField({ id: 'color', label: 'Text color', type: 'color', value: rgbToHex($block.find('.el_text').css('color')) }));
			$fields.append(buildField({ id: 'fontSize', label: 'Font size (px)', type: 'number', value: parseInt($block.find('.el_text').css('font-size')) || 18, min: 10, max: 96 }));
			$fields.append(buildField({ id: 'fontFamily', label: 'Font family', type: 'select', value: currentFont, options: [
				{value: 'Open Sans', label: 'Open Sans'}, {value: 'Roboto', label: 'Roboto'}, {value: 'Inter', label: 'Inter'}, {value: 'Poppins', label: 'Poppins'}, {value: 'Lato', label: 'Lato'}, {value: 'Montserrat', label: 'Montserrat'}, {value: 'Source Sans Pro', label: 'Source Sans Pro'}, {value: 'Nunito', label: 'Nunito'}, {value: 'Playfair Display', label: 'Playfair Display'}, {value: 'Merriweather', label: 'Merriweather'}
			]}));
			if ($block.closest('.el_group').length) {
				const $item = $block;
				$fields.append(`
					<div class="inspector_group">
						<div class="inspector_group_title">Flex Item</div>
						<div class="inspector_grid_2">
							${buildField({ id: 'flexGrow', label: 'Flex grow', type: 'number', value: parseInt($item.css('flex-grow')) || 0, min:0, max:10 })}
							${buildField({ id: 'flexShrink', label: 'Flex shrink', type: 'number', value: parseInt($item.css('flex-shrink')) || 1, min:0, max:10 })}
						</div>
						${buildSizeFields('basis', 'Flex basis', $item.css('flex-basis')||'auto')}
						${buildField({ id: 'alignSelf', label: 'Align self', type: 'select', value: ($item.css('align-self')||'auto'), options:[{value:'auto',label:'auto'},{value:'flex-start',label:'flex-start'},{value:'center',label:'center'},{value:'flex-end',label:'flex-end'},{value:'stretch',label:'stretch'}] })}
					</div>
				`);
			}
		} else if (meta.type === 'button') {
			const $btn = $block.find('.el_button');
			const currentFont = $btn.css('font-family').replace(/['"]/g, '').split(',')[0].trim();
			$fields.append(`<div class="inspector_field"><div class="inspector_label">Text</div><div style="padding: 8px; background: #f0f8ff; border: 1px solid #007bff; border-radius: 4px; font-size: 13px; color: #0056b3;">💡 Двойной клик для редактирования</div></div>`);
			$fields.append(buildField({ id: 'href', label: 'Link URL', value: $btn.attr('href') || '#' }));
			$fields.append(buildField({ id: 'bg', label: 'Background', type: 'color', value: rgbToHex($btn.css('background-color')) }));
			$fields.append(buildField({ id: 'textColor', label: 'Text color', type: 'color', value: rgbToHex($btn.css('color')) }));
			$fields.append(buildField({ id: 'fontSize', label: 'Font size (px)', type: 'number', value: parseInt($btn.css('font-size')) || 12, min: 8, max: 48 }));
			$fields.append(buildField({ id: 'fontFamily', label: 'Font family', type: 'select', value: currentFont, options: [
				{value: 'Open Sans', label: 'Open Sans'}, {value: 'Roboto', label: 'Roboto'}, {value: 'Inter', label: 'Inter'}, {value: 'Poppins', label: 'Poppins'}, {value: 'Lato', label: 'Lato'}, {value: 'Montserrat', label: 'Montserrat'}, {value: 'Source Sans Pro', label: 'Source Sans Pro'}, {value: 'Nunito', label: 'Nunito'}, {value: 'Playfair Display', label: 'Playfair Display'}, {value: 'Merriweather', label: 'Merriweather'}
			]}));
			$fields.append(buildField({ id: 'radius', label: 'Border radius (px)', type: 'number', value: parseInt($btn.css('border-radius')) || 4, min: 0, max: 48 }));
			$fields.append(buildField({ id: 'borderColor', label: 'Border color', type: 'color', value: rgbToHex($btn.css('border-color')) }));
			$fields.append(buildField({ id: 'borderWidth', label: 'Border width (px)', type: 'number', value: parseInt($btn.css('border-width')) || 0, min: 0, max: 12 }));
			$fields.append(buildField({ id: 'paddingX', label: 'Padding X (px)', type: 'number', value: parseInt($btn.css('padding-left')) || 24, min: 0, max: 80 }));
			$fields.append(buildField({ id: 'paddingY', label: 'Padding Y (px)', type: 'number', value: parseInt($btn.css('padding-top')) || 12, min: 0, max: 80 }));
			$fields.append(buildSizeFields('width', 'Width', $btn.css('width')));
			if ($block.closest('.el_group').length) {
				const $item = $block;
				$fields.append(`
					<div class="inspector_group">
						<div class="inspector_group_title">Flex Item</div>
						<div class="inspector_grid_2">
							${buildField({ id: 'flexGrow', label: 'Flex grow', type: 'number', value: parseInt($item.css('flex-grow')) || 0, min:0, max:10 })}
							${buildField({ id: 'flexShrink', label: 'Flex shrink', type: 'number', value: parseInt($item.css('flex-shrink')) || 1, min:0, max:10 })}
						</div>
						${buildSizeFields('basis', 'Flex basis', $item.css('flex-basis')||'auto')}
						${buildField({ id: 'alignSelf', label: 'Align self', type: 'select', value: ($item.css('align-self')||'auto'), options:[{value:'auto',label:'auto'},{value:'flex-start',label:'flex-start'},{value:'center',label:'center'},{value:'flex-end',label:'flex-end'},{value:'stretch',label:'stretch'}] })}
					</div>
				`);
			}
		} else if (meta.type === 'image') {
			const $img = $block.find('.el_image');
			$fields.append(`
				<div class="inspector_field">
					<label class="inspector_label">Изображение</label>
					<div id="imageDropZone" style="border: 2px dashed #ddd; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;">
						<div style="color: #666; font-size: 14px;">
							Перетащите изображение сюда или 
							<span style="color: #007bff; text-decoration: underline;">выберите файл</span>
						</div>
						<input type="file" id="imageFile" accept="image/*" style="display: none;">
					</div>
					<input type="text" id="imageUrl" placeholder="Или введите URL изображения" class="input">
				</div>
			`);
			$fields.append(buildSizeFields('width', 'Width', $img.css('width')));
			$fields.append(buildSizeFields('height', 'Height', $img.css('height')));
			$fields.append(buildField({ id: 'bg', label: 'Background', type: 'color', value: rgbToHex($img.css('background-color')) }));
			$fields.append(buildField({ id: 'radius', label: 'Border radius (px)', type: 'number', value: parseInt($img.css('border-radius')) || 8, min: 0, max: 200 }));
			$fields.append(buildField({ id: 'borderColor', label: 'Border color', type: 'color', value: rgbToHex($img.css('border-color')) }));
			$fields.append(buildField({ id: 'borderWidth', label: 'Border width (px)', type: 'number', value: parseInt($img.css('border-width')) || 0, min: 0, max: 20 }));
			if ($block.closest('.el_group').length) {
				const $item = $block;
				$fields.append(`
					<div class="inspector_group">
						<div class="inspector_group_title">Flex Item</div>
						<div class="inspector_grid_2">
							${buildField({ id: 'flexGrow', label: 'Flex grow', type: 'number', value: parseInt($item.css('flex-grow')) || 0, min:0, max:10 })}
							${buildField({ id: 'flexShrink', label: 'Flex shrink', type: 'number', value: parseInt($item.css('flex-shrink')) || 1, min:0, max:10 })}
						</div>
						${buildSizeFields('basis', 'Flex basis', $item.css('flex-basis')||'auto')}
						${buildField({ id: 'alignSelf', label: 'Align self', type: 'select', value: ($item.css('align-self')||'auto'), options:[{value:'auto',label:'auto'},{value:'flex-start',label:'flex-start'},{value:'center',label:'center'},{value:'flex-end',label:'flex-end'},{value:'stretch',label:'stretch'}] })}
					</div>
				`);
			}
		} else if (meta.type === 'block') {
			const $inner = $block.find('.el_block');
			$fields.append(buildField({ id: 'height', label: 'Height (px)', type: 'number', value: parseInt($inner.css('height')) || 100, min: 20, max: 1200 }));
			$fields.append(buildField({ id: 'bg', label: 'Background', type: 'color', value: rgbToHex($inner.css('background-color')) }));
			$fields.append(buildField({ id: 'borderColor', label: 'Border color', type: 'color', value: rgbToHex($inner.css('border-color')) }));
			$fields.append(buildField({ id: 'radius', label: 'Border radius (px)', type: 'number', value: parseInt($inner.css('border-radius')) || 8, min: 0, max: 200 }));
			$fields.append(buildField({ id: 'borderWidth', label: 'Border width (px)', type: 'number', value: parseInt($inner.css('border-width')) || 2, min: 0, max: 20 }));
			if ($block.closest('.el_group').length) {
				const $item = $block;
				$fields.append(`
					<div class="inspector_group">
						<div class="inspector_group_title">Flex Item</div>
						<div class="inspector_grid_2">
							${buildField({ id: 'flexGrow', label: 'Flex grow', type: 'number', value: parseInt($item.css('flex-grow')) || 0, min:0, max:10 })}
							${buildField({ id: 'flexShrink', label: 'Flex shrink', type: 'number', value: parseInt($item.css('flex-shrink')) || 1, min:0, max:10 })}
						</div>
						${buildSizeFields('basis', 'Flex basis', $item.css('flex-basis')||'auto')}
						${buildField({ id: 'alignSelf', label: 'Align self', type: 'select', value: ($item.css('align-self')||'auto'), options:[{value:'auto',label:'auto'},{value:'flex-start',label:'flex-start'},{value:'center',label:'center'},{value:'flex-end',label:'flex-end'},{value:'stretch',label:'stretch'}] })}
					</div>
				`);
			}
		} else if (meta.type === 'group') {
			const $grp = $block.find('.el_group');
			$fields.append(`
				<div class="inspector_group">
					<div class="inspector_group_title">Flex Container</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'direction', label: 'Direction', type: 'select', value: ($grp.css('flex-direction')||'row'), options:[{value:'row',label:'row'},{value:'column',label:'column'}] })}
						${buildField({ id: 'wrap', label: 'Wrap', type: 'select', value: ($grp.css('flex-wrap')||'wrap'), options:[{value:'nowrap',label:'nowrap'},{value:'wrap',label:'wrap'}] })}
					</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'justify', label: 'Justify', type: 'select', value: ($grp.css('justify-content')||'flex-start'), options:[{value:'flex-start',label:'flex-start'},{value:'center',label:'center'},{value:'space-between',label:'space-between'},{value:'space-around',label:'space-around'},{value:'flex-end',label:'flex-end'}] })}
						${buildField({ id: 'align', label: 'Align', type: 'select', value: ($grp.css('align-items')||'flex-start'), options:[{value:'flex-start',label:'flex-start'},{value:'center',label:'center'},{value:'stretch',label:'stretch'},{value:'flex-end',label:'flex-end'}] })}
					</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'gap', label: 'Gap (px)', type: 'number', value: parseInt($grp.css('gap')) || 16, min: 0, max: 120 })}
						${buildField({ id: 'padding', label: 'Padding (px)', type: 'number', value: parseInt($grp.css('padding')) || 16, min: 0, max: 120 })}
					</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'bg', label: 'Background', type: 'color', value: rgbToHex($grp.css('background-color')) })}
						${buildField({ id: 'radius', label: 'Border radius (px)', type: 'number', value: parseInt($grp.css('border-radius')) || 12, min: 0, max: 200 })}
					</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'borderColor', label: 'Border color', type: 'color', value: rgbToHex($grp.css('border-color')) })}
						${buildField({ id: 'borderWidth', label: 'Border width (px)', type: 'number', value: parseInt($grp.css('border-width')) || 2, min: 0, max: 20 })}
					</div>
					${buildSizeFields('width', 'Width', $block.css('width'))}
				</div>
			`);
		} else if (meta.type === 'canvas') {
			const $cv = $block;
			$fields.append(`
				<div class="inspector_group">
					<div class="inspector_group_title">Canvas</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'cvBg', label: 'Background', type: 'color', value: rgbToHex($cv.css('background-color')) })}
						${buildField({ id: 'cvRadius', label: 'Border radius (px)', type: 'number', value: parseInt($cv.css('border-radius')) || 12, min: 0, max: 64 })}
					</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'cvPadding', label: 'Padding (px)', type: 'number', value: parseInt($cv.css('padding')) || 40, min: 0, max: 160 })}
						${buildField({ id: 'cvMaxWidth', label: 'Max width (px)', type: 'number', value: parseInt($cv.css('max-width')) || 1080, min: 320, max: 2000 })}
					</div>
				</div>
			`);
		} else if (meta.type === 'product') {
			const $img = $block.find('.el_product_image');
			const $title = $block.find('.el_product_title');
			const $desc = $block.find('.el_product_desc');
			const $price = $block.find('.el_product_price');
			const $btn1 = $block.find('.el_product_actions .el_button').eq(0);
			const $btn2 = $block.find('.el_product_actions .el_button').eq(1);
			$fields.append(`
				<div class="inspector_group">
					<div class="inspector_group_title">Content</div>
					${buildField({ id: 'pTitle', label: 'Title', value: $title.text() })}
					${buildTextarea({ id: 'pDesc', label: 'Description', value: $desc.text() })}
					<div class="inspector_grid_2">
						${buildField({ id: 'pPrice', label: 'Price', value: $price.text() })}
						${buildField({ id: 'pImage', label: 'Image URL', value: '' })}
					</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'pImgW', label: 'Image width (px)', type: 'number', value: parseInt($img.css('width')) || 200, min: 40, max: 600 })}
						${buildField({ id: 'pImgH', label: 'Image height (px)', type: 'number', value: parseInt($img.css('height')) || 200, min: 40, max: 600 })}
					</div>
				</div>
				<div class="inspector_group">
					<div class="inspector_group_title">Typography</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'pTitleColor', label: 'Title color', type: 'color', value: rgbToHex($title.css('color')) })}
						${buildField({ id: 'pTitleSize', label: 'Title size (px)', type: 'number', value: parseInt($title.css('font-size')) || 21, min: 12, max: 48 })}
					</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'pDescColor', label: 'Description color', type: 'color', value: rgbToHex($desc.css('color')) })}
						${buildField({ id: 'pDescSize', label: 'Description size (px)', type: 'number', value: parseInt($desc.css('font-size')) || 18, min: 10, max: 32 })}
					</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'pPriceColor', label: 'Price color', type: 'color', value: rgbToHex($price.css('color')) })}
						${buildField({ id: 'pPriceSize', label: 'Price size (px)', type: 'number', value: parseInt($price.css('font-size')) || 21, min: 12, max: 48 })}
					</div>
				</div>
				<div class="inspector_group">
					<div class="inspector_group_title">Buttons</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'pBtn1Label', label: 'Primary label', value: ($btn1.text()||'Buy') })}
						${buildField({ id: 'pBtn1Href', label: 'Primary link', value: ($btn1.attr('href')||'#') })}
					</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'pBtn1Bg', label: 'Primary BG', type: 'color', value: rgbToHex($btn1.css('background-color')) })}
						${buildField({ id: 'pBtn1Color', label: 'Primary color', type: 'color', value: rgbToHex($btn1.css('color')) })}
					</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'pBtn2Label', label: 'Secondary label', value: ($btn2.text()||'Details') })}
						${buildField({ id: 'pBtn2Href', label: 'Secondary link', value: ($btn2.attr('href')||'#') })}
					</div>
					<div class="inspector_grid_2">
						${buildField({ id: 'pBtn2Color', label: 'Secondary color', type: 'color', value: rgbToHex($btn2.css('color')) })}
						${buildField({ id: 'pBtn2Show', label: 'Show secondary', type: 'select', value: ($btn2.length>0?'yes':'no'), options:[{value:'yes',label:'Yes'},{value:'no',label:'No'}] })}
					</div>
				</div>
			`);
			if ($block.closest('.el_group').length) {
				const $item = $block;
				$fields.append(`
					<div class="inspector_group">
						<div class="inspector_group_title">Flex Item</div>
						<div class="inspector_grid_2">
							${buildField({ id: 'flexGrow', label: 'Flex grow', type: 'number', value: parseInt($item.css('flex-grow')) || 0, min:0, max:10 })}
							${buildField({ id: 'flexShrink', label: 'Flex shrink', type: 'number', value: parseInt($item.css('flex-shrink')) || 1, min:0, max:10 })}
						</div>
						${buildSizeFields('basis', 'Flex basis', $item.css('flex-basis')||'auto')}
						${buildField({ id: 'alignSelf', label: 'Align self', type: 'select', value: ($item.css('align-self')||'auto'), options:[{value:'auto',label:'auto'},{value:'flex-start',label:'flex-start'},{value:'center',label:'center'},{value:'flex-end',label:'flex-end'},{value:'stretch',label:'stretch'}] })}
					</div>
				`);
			}
		} else if (meta.type === 'link') {
			const $lnk = $block.find('.el_link');
			const currentFont = $lnk.css('font-family').replace(/['"]/g, '').split(',')[0].trim();
			$fields.append(`<div class="inspector_field"><div class="inspector_label">Text</div><div style="padding: 8px; background: #f0f8ff; border: 1px solid #007bff; border-radius: 4px; font-size: 13px; color: #0056b3;">💡 Двойной клик для редактирования</div></div>`);
			$fields.append(buildField({ id: 'href', label: 'Link URL', value: $lnk.attr('href') || '#' }));
			$fields.append(buildField({ id: 'color', label: 'Text color', type: 'color', value: rgbToHex($lnk.css('color')) }));
			$fields.append(buildField({ id: 'fontSize', label: 'Font size (px)', type: 'number', value: parseInt($lnk.css('font-size')) || 18, min: 10, max: 96 }));
			$fields.append(buildField({ id: 'fontFamily', label: 'Font family', type: 'select', value: currentFont, options: [
				{value: 'Open Sans', label: 'Open Sans'}, {value: 'Roboto', label: 'Roboto'}, {value: 'Inter', label: 'Inter'}, {value: 'Poppins', label: 'Poppins'}, {value: 'Lato', label: 'Lato'}, {value: 'Montserrat', label: 'Montserrat'}, {value: 'Source Sans Pro', label: 'Source Sans Pro'}, {value: 'Nunito', label: 'Nunito'}, {value: 'Playfair Display', label: 'Playfair Display'}, {value: 'Merriweather', label: 'Merriweather'}
			]}));
		} else if (meta.type === 'list') {
			const $ul = $block.find('.el_list');
			const currentFont = $ul.css('font-family').replace(/['"]/g, '').split(',')[0].trim();
			$fields.append(buildTextarea({ id: 'items', label: 'Items (one per line)', value: $ul.children().map(function(){return $(this).text();}).get().join('\n') }));
			$fields.append(buildField({ id: 'color', label: 'Text color', type: 'color', value: rgbToHex($ul.css('color')) }));
			$fields.append(buildField({ id: 'fontSize', label: 'Font size (px)', type: 'number', value: parseInt($ul.css('font-size')) || 18, min: 10, max: 96 }));
			$fields.append(buildField({ id: 'fontFamily', label: 'Font family', type: 'select', value: currentFont, options: [
				{value: 'Open Sans', label: 'Open Sans'}, {value: 'Roboto', label: 'Roboto'}, {value: 'Inter', label: 'Inter'}, {value: 'Poppins', label: 'Poppins'}, {value: 'Lato', label: 'Lato'}, {value: 'Montserrat', label: 'Montserrat'}, {value: 'Source Sans Pro', label: 'Source Sans Pro'}, {value: 'Nunito', label: 'Nunito'}, {value: 'Playfair Display', label: 'Playfair Display'}, {value: 'Merriweather', label: 'Merriweather'}
			]}));
		} else if (meta.type === 'separator') {
			const $sep = $block.find('.el_separator');
			$fields.append(buildField({ id: 'height', label: 'Thickness (px)', type: 'number', value: parseInt($sep.css('height')) || 2, min: 1, max: 20 }));
			$fields.append(buildField({ id: 'bg', label: 'Color', type: 'color', value: rgbToHex($sep.css('background-color')) }));
		} else if (meta.type === 'product-card') {
			const $card = $block.find('.el_product_card');
			$fields.append(`<div class="inspector_group"><div class="inspector_group_title">Card Settings</div></div>`);
			$fields.append(buildField({ id: 'cardWidth', label: 'Width (px)', type: 'number', value: parseInt($card.css('width')) || 300, min: 200, max: 600 }));
			$fields.append(buildField({ id: 'cardBg', label: 'Background', type: 'color', value: rgbToHex($card.css('background-color')) }));
			$fields.append(buildField({ id: 'cardRadius', label: 'Border radius (px)', type: 'number', value: parseInt($card.css('border-radius')) || 12, min: 0, max: 24 }));
			
			$fields.append(`<div class="inspector_group"><div class="inspector_group_title">Product Text</div></div>`);
			$fields.append(`<div class="inspector_field"><div class="inspector_label">All Text Elements</div><div style="padding: 8px; background: #f0f8ff; border: 1px solid #007bff; border-radius: 4px; font-size: 13px; color: #0056b3;">💡 Двойной клик на любой текст для редактирования</div></div>`);
		}

		bindInspector($block, meta.type);
		if (!sidebarIsOpen) {
			openSidebar('inspector');
		} else {
			$('.general_sidebar_right').addClass('inspector-open');
			$('#inspector').show();
		}
	}

	function rgbToHex(rgb) {
		if (!rgb) return '#000000';
		if (rgb.startsWith('#')) return rgb;
		const m = rgb.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
		if (!m) return '#000000';
		const r = parseInt(m[1]).toString(16).padStart(2, '0');
		const g = parseInt(m[2]).toString(16).padStart(2, '0');
		const b = parseInt(m[3]).toString(16).padStart(2, '0');
		return `#${r}${g}${b}`;
	}

	function bindInspector($block, type) {
		$('#inspectorFields').off('input change');
		$('#inspectorFields').on('input change', 'input, textarea, select', function() {
			const field = $(this).data('field');
			if (!field) return;
			if (type === 'text') {
				const $t = $block.find('.el_text');
				if (field === 'color') setStyleForMode($t, 'color', $(this).val());
				if (field === 'fontSize') setStyleForMode($t, 'font-size', `${$(this).val()}px`);
				if (field === 'fontFamily') setStyleForMode($t, 'font-family', $(this).val());
			}
			if (type === 'button') {
				const $btn = $block.find('.el_button');
				if (field === 'widthValue' || field === 'widthUnit') { const size = getSizeFromFields('width'); setStyleForMode($btn, 'width', size); }
				if (field === 'href') $btn.attr('href', $(this).val());
				if (field === 'bg') setStyleForMode($btn, 'background-color', $(this).val());
				if (field === 'textColor') setStyleForMode($btn, 'color', $(this).val());
				if (field === 'fontSize') setStyleForMode($btn, 'font-size', `${$(this).val()}px`);
				if (field === 'fontFamily') setStyleForMode($btn, 'font-family', $(this).val());
				if (field === 'radius') setStyleForMode($btn, 'border-radius', `${$(this).val()}px`);
				if (field === 'borderColor') setStyleForMode($btn, 'border-color', $(this).val());
				if (field === 'borderWidth') { setStyleForMode($btn, 'border-width', `${$(this).val()}px`); setStyleForMode($btn, 'border-style', `${parseInt($(this).val())>0?'solid':'none'}`); }
				if (field === 'paddingX') { setStyleForMode($btn, 'padding-left', `${$(this).val()}px`); setStyleForMode($btn, 'padding-right', `${$(this).val()}px`); }
				if (field === 'paddingY') { setStyleForMode($btn, 'padding-top', `${$(this).val()}px`); setStyleForMode($btn, 'padding-bottom', `${$(this).val()}px`); }
			}
			if (type === 'image') {
				const $img = $block.find('.el_image');
				if (field === 'widthValue' || field === 'widthUnit') { const size = getSizeFromFields('width'); setStyleForMode($img, 'width', size); }
				if (field === 'heightValue' || field === 'heightUnit') { const size = getSizeFromFields('height'); setStyleForMode($img, 'height', size); }
				if (field === 'bg') setStyleForMode($img, 'background-color', $(this).val());
				if (field === 'radius') setStyleForMode($img, 'border-radius', `${$(this).val()}px`);
				if (field === 'borderColor') setStyleForMode($img, 'border-color', $(this).val());
				if (field === 'borderWidth') { setStyleForMode($img, 'border-width', `${$(this).val()}px`); setStyleForMode($img, 'border-style', `${parseInt($(this).val())>0?'solid':'none'}`); }
			}
			if (type === 'block') {
				const $inner = $block.find('.el_block');
				if (field === 'height') setStyleForMode($inner, 'height', `${$(this).val()}px`);
				if (field === 'bg') setStyleForMode($inner, 'background-color', $(this).val());
				if (field === 'borderColor') setStyleForMode($inner, 'border-color', $(this).val());
				if (field === 'radius') setStyleForMode($inner, 'border-radius', `${$(this).val()}px`);
				if (field === 'borderWidth') { setStyleForMode($inner, 'border-width', `${$(this).val()}px`); setStyleForMode($inner, 'border-style', `${parseInt($(this).val())>0?'solid':'dashed'}`); }
			}
			if (type === 'group') {
				const $grp = $block.find('.el_group');
				if (field === 'widthValue' || field === 'widthUnit') { const size = getSizeFromFields('width'); setStyleForMode($block, 'width', size); }
				if (field === 'direction') setStyleForMode($grp, 'flex-direction', $(this).val());
				if (field === 'wrap') setStyleForMode($grp, 'flex-wrap', $(this).val());
				if (field === 'justify') setStyleForMode($grp, 'justify-content', $(this).val());
				if (field === 'align') setStyleForMode($grp, 'align-items', $(this).val());
				if (field === 'gap') setStyleForMode($grp, 'gap', `${$(this).val()}px`);
				if (field === 'padding') setStyleForMode($grp, 'padding', `${$(this).val()}px`);
				if (field === 'bg') setStyleForMode($grp, 'background-color', $(this).val());
				if (field === 'radius') setStyleForMode($grp, 'border-radius', `${$(this).val()}px`);
				if (field === 'borderColor') setStyleForMode($grp, 'border-color', $(this).val());
				if (field === 'borderWidth') { setStyleForMode($grp, 'border-width', `${$(this).val()}px`); setStyleForMode($grp, 'border-style', `${parseInt($(this).val())>0?'solid':'dashed'}`); }
			}
			// Generic flex item fields (applied to container block itself)
			if (['text','button','image','block'].includes(type)) {
				const $item = $block; // top-level wrapper acts as flex item
				if (field === 'flexGrow') $item.css('flex-grow', $(this).val());
				if (field === 'flexShrink') $item.css('flex-shrink', $(this).val());
				if (field === 'alignSelf') $item.css('align-self', $(this).val());
				if (field === 'basisValue' || field === 'basisUnit') { const size = getSizeFromFields('basis'); $item.css('flex-basis', size || 'auto'); }
			}
			if (type === 'link') {
				const $lnk = $block.find('.el_link');
				if (field === 'href') $lnk.attr('href', $(this).val());
				if (field === 'color') setStyleForMode($lnk, 'color', $(this).val());
				if (field === 'fontSize') setStyleForMode($lnk, 'font-size', `${$(this).val()}px`);
				if (field === 'fontFamily') setStyleForMode($lnk, 'font-family', $(this).val());
			}
			if (type === 'list') {
				const $ul = $block.find('.el_list');
				if (field === 'items') {
					const lines = $(this).val().split(/\n/).filter(Boolean);
					$ul.empty();
					lines.forEach(txt => $ul.append(`<li>${$('<div>').text(txt).html()}</li>`));
				}
				if (field === 'color') setStyleForMode($ul, 'color', $(this).val());
				if (field === 'fontSize') setStyleForMode($ul, 'font-size', `${$(this).val()}px`);
				if (field === 'fontFamily') setStyleForMode($ul, 'font-family', $(this).val());
			}
			if (type === 'separator') {
				const $sep = $block.find('.el_separator');
				if (field === 'height') setStyleForMode($sep, 'height', `${$(this).val()}px`);
				if (field === 'bg') setStyleForMode($sep, 'background-color', $(this).val());
			}
			if (type === 'product-card') {
				const $card = $block.find('.el_product_card');
				if (field === 'cardWidth') setStyleForMode($card, 'width', `${$(this).val()}px`);
				if (field === 'cardBg') setStyleForMode($card, 'background-color', $(this).val());
				if (field === 'cardRadius') setStyleForMode($card, 'border-radius', `${$(this).val()}px`);
			}
			if (type === 'product') {
				const $card = $block.find('.el_product');
				const $img = $block.find('.el_product_image');
				const $title = $block.find('.el_product_title');
				const $desc = $block.find('.el_product_desc');
				const $price = $block.find('.el_product_price');
				const $btn1 = $block.find('.el_product_actions .el_button').eq(0);
				const $btn2 = $block.find('.el_product_actions .el_button').eq(1);
				if (field === 'pTitle') $title.text($(this).val());
				if (field === 'pDesc') $desc.text($(this).val());
				if (field === 'pPrice') $price.text($(this).val());
				if (field === 'pImage') {
					const url = $(this).val();
					if (url) { $img.css('background-image', `url(${url})`); $img.children().hide(); }
					else { $img.css('background-image', 'none'); if ($img.children().length === 0) { $img.append('<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#999;font-size:14px;">Image</div>'); } else { $img.children().show(); } }
				}
				if (field === 'pImgW') setStyleForMode($img, 'width', `${$(this).val()}px`);
				if (field === 'pImgH') setStyleForMode($img, 'height', `${$(this).val()}px`);
				if (field === 'pBg') setStyleForMode($card, 'background-color', $(this).val());
				if (field === 'pRadius') setStyleForMode($card, 'border-radius', `${$(this).val()}px`);
				if (field === 'pBorder') setStyleForMode($card, 'border-color', $(this).val());
				if (field === 'pBorderW') { setStyleForMode($card, 'border-width', `${$(this).val()}px`); setStyleForMode($card, 'border-style', `${parseInt($(this).val())>0?'solid':'none'}`); }
				if (field === 'pTitleColor') setStyleForMode($title, 'color', $(this).val());
				if (field === 'pTitleSize') setStyleForMode($title, 'font-size', `${$(this).val()}px`);
				if (field === 'pDescColor') setStyleForMode($desc, 'color', $(this).val());
				if (field === 'pDescSize') setStyleForMode($desc, 'font-size', `${$(this).val()}px`);
				if (field === 'pPriceColor') setStyleForMode($price, 'color', $(this).val());
				if (field === 'pPriceSize') setStyleForMode($price, 'font-size', `${$(this).val()}px`);
				if (field === 'pBtn1Label') $btn1.text($(this).val());
				if (field === 'pBtn1Href') $btn1.attr('href', $(this).val());
				if (field === 'pBtn1Bg') setStyleForMode($btn1, 'background-color', $(this).val());
				if (field === 'pBtn1Color') setStyleForMode($btn1, 'color', $(this).val());
				if (field === 'pBtn2Label') $btn2.text($(this).val());
				if (field === 'pBtn2Href') $btn2.attr('href', $(this).val());
				if (field === 'pBtn2Color') setStyleForMode($btn2, 'color', $(this).val());
				if (field === 'pBtn2Show') { if ($(this).val()==='no') $btn2.hide(); else $btn2.show(); }
			}
			if (type === 'canvas') {
				const $cv = $block;
				if (field === 'cvBg') setStyleForMode($cv, 'background-color', $(this).val());
				if (field === 'cvRadius') setStyleForMode($cv, 'border-radius', `${$(this).val()}px`);
				if (field === 'cvPadding') setStyleForMode($cv, 'padding', `${$(this).val()}px`);
				if (field === 'cvMaxWidth') setStyleForMode($cv, 'max-width', `${$(this).val()}px`);
			}
		});

		// Special handlers for image upload
		if (type === 'image') {
			// File input handler
			$('#imageFile').off('change').on('change', async function(e) {
				const file = e.target.files[0];
				if (!file) return;
				
				const $img = $block.find('.el_image');
				
				// Show loading state
				$img.html('<div style="text-align:center; color:#666; font-size:14px;">Загрузка...</div>');
				
				try {
					const formData = new FormData();
					formData.append('image', file);
					
					const response = await fetch('api/upload_image.php', {
						method: 'POST',
						body: formData
					});
					
					const result = await response.json();
					
					if (result.success) {
						$img.html(`<img src="${result.url}" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">`);
						markProjectAsChanged();
					} else {
						throw new Error(result.error || 'Upload failed');
					}
				} catch (error) {
					console.error('Upload error:', error);
					alert('Ошибка загрузки: ' + error.message);
					$img.html('<div style="text-align:center; color:#999; font-size:14px;">Image</div>');
				}
			});
			
			$('#imageUrl').off('input').on('input', function() {
				const url = $(this).val();
				const $img = $block.find('.el_image');
				if (url) {
					$img.html(`<img src="${url}" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">`);
				} else {
					$img.html('<div style="text-align:center; color:#999; font-size:14px;">Image</div>');
				}
				markProjectAsChanged();
			});
			
			// Drop zone handlers
			const $dropZone = $('#imageDropZone');
			const $fileInput = $('#imageFile');
			
			// Click to select file
			$dropZone.off('click').on('click', function() {
				$fileInput.trigger('click');
			});
			
			// Drag & drop handlers
			$dropZone.off('dragover dragenter').on('dragover dragenter', function(e) {
				e.preventDefault();
				e.stopPropagation();
				$(this).css({
					'border-color': '#007bff',
					'background-color': '#f8f9ff'
				});
			});
			
			$dropZone.off('dragleave').on('dragleave', function(e) {
				e.preventDefault();
				e.stopPropagation();
				$(this).css({
					'border-color': '#ddd',
					'background-color': 'transparent'
				});
			});
			
			$dropZone.off('drop').on('drop', async function(e) {
				e.preventDefault();
				e.stopPropagation();
				
				$(this).css({
					'border-color': '#ddd',
					'background-color': 'transparent'
				});
				
				const files = e.originalEvent.dataTransfer.files;
				if (files.length > 0) {
					const file = files[0];
					if (file.type.startsWith('image/')) {
						// Trigger the same upload process as file input
						const $img = $block.find('.el_image');
						$img.html('<div style="text-align:center; color:#666; font-size:14px;">Загрузка...</div>');
						
						try {
							const formData = new FormData();
							formData.append('image', file);
							
							const response = await fetch('api/upload_image.php', {
								method: 'POST',
								body: formData
							});
							
							const result = await response.json();
							
							if (result.success) {
								$img.html(`<img src="${result.url}" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">`);
								markProjectAsChanged();
							} else {
								throw new Error(result.error || 'Upload failed');
							}
						} catch (error) {
							console.error('Upload error:', error);
							alert('Ошибка загрузки: ' + error.message);
							$img.html('<div style="text-align:center; color:#999; font-size:14px;">Image</div>');
						}
					} else {
						alert('Пожалуйста, выберите файл изображения');
					}
				}
			});
		}

		$('#inspectorDeselect').off('click').on('click', function() { deselectElement(); });
		$('#inspectorRemove').off('click').on('click', function() { 
			if ($block && $block.length) { 
				$block.remove(); 
				deselectElement(); 
				
				// Save state after element removal
				const projectState = getProjectState();
				if (projectState && projectState.id) {
					const currentCanvas = serializeCanvasOuterHtml();
					projectState.canvas = currentCanvas;
					projectState.lastModified = Date.now();
					localStorage.setItem('currentProject', JSON.stringify(projectState));
					markProjectAsChanged();
				}
			} 
		});
	}

	function deselectElement() {
		if ($selectedBlock) {
			$selectedBlock.removeClass('edit_mode');
			$selectedBlock = null;
		}
		$('#inspector').hide();
		$('.general_sidebar_right').removeClass('inspector-open');
	}

	// Click to select on canvas elements with double-click detection
	window.clickTimeout = null;
	$(document).on('click', '.general_canva [class*="el_"]', function(e) {
		const $element = $(this);
		
		// Clear any existing timeout
		if (window.clickTimeout) {
			clearTimeout(window.clickTimeout);
			window.clickTimeout = null;
		}
		
		// Skip if this is part of a double-click action or element is being edited
		if (e.detail === 2 || $element.hasClass('inline-editing') || $element.closest('.inline-editing').length) {
			return;
		}
		
		// Check if target is an editable element that could receive double-click
		const editableSelectors = [
			'.el_text',
			'.el_link', 
			'.product_title',
			'.product_description',
			'.product_badge',
			'.product_price',
			'.product_old_price',
			'.product_discount',
			'.product_buy_btn',
			'.el_button'
		];
		
		const isEditableElement = editableSelectors.some(selector => 
			$element.is(selector) || $element.find(selector).length > 0
		);
		
		// If it's an editable element, add a delay to allow for potential double-click
		const delay = isEditableElement ? 300 : 50;
		
		window.clickTimeout = setTimeout(() => {
			let $block = $element;
			if (!/(_block|_container)$/.test($block.attr('class'))) {
				$block = $block.closest('.el_text_block, .el_button_block, .el_image_block, .el_block_container, .el_group_block, .el_product_block, .el_product_card_block, .el_link_block, .el_list_block, .el_separator_block');
			}
			if ($block && $block.length) {
				// Don't open inspector if element is being edited
				if ($block.hasClass('inline-editing') || $block.find('.inline-editing').length) {
					return;
				}
				
				if ($selectedBlock && $selectedBlock[0] !== $block[0]) { $selectedBlock.removeClass('edit_mode'); }
				$selectedBlock = $block;
				$block.addClass('edit_mode');
				showInspectorFor($block);
			}
			window.clickTimeout = null;
		}, delay);
		
		e.stopPropagation();
	});

	// Click empty area of canvas to open canvas inspector
	$(document).on('click', '.general_canva', function(e) {
		const clickedInsideElement = $(e.target).closest('.general_canva [class*="el_"]').length > 0;
		if (clickedInsideElement) return;
		if ($selectedBlock && !$selectedBlock.hasClass('general_canva')) { $selectedBlock.removeClass('edit_mode'); }
		$selectedBlock = $('.general_canva');
		$selectedBlock.addClass('edit_mode');
		showInspectorFor($selectedBlock);
		e.stopPropagation();
	});

	// Deselect by clicking outside canvas blocks or pressing Escape
	$(document).on('click', function(e) {
		const clickedInsideCanvas = $(e.target).closest('.general_canva [class*="el_"]').length > 0;
		const clickedInsideInspector = $(e.target).closest('#inspector').length > 0;
		if (!clickedInsideCanvas && !clickedInsideInspector && $selectedBlock) { deselectElement(); }
	});

	$(document).on('keydown', function(e) { if (e.key === 'Escape') { deselectElement(); } });
});




// Add some basic styles for the image element
$('<style>')
    .appendTo('head')
    .text(`
        .el_image {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        .el_image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        
        /* Enhanced duplicate button styles */
        .duplicate-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.9), rgba(0, 86, 179, 0.9));
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .duplicate-btn:hover {
            background: linear-gradient(135deg, rgba(0, 86, 179, 1), rgba(0, 123, 255, 1));
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
        }
        
        .duplicate-btn:active {
            transform: scale(0.95);
        }

        /* Quick delete button styles */
        .quick-delete-btn {
            position: absolute;
            top: 8px;
            right: 42px;
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.9), rgba(179, 43, 56, 0.9));
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .quick-delete-btn:hover {
            background: linear-gradient(135deg, rgba(179, 43, 56, 1), rgba(220, 53, 69, 1));
            transform: scale(1.15) rotate(-5deg);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        }
        
        .quick-delete-btn:active {
            transform: scale(0.95);
        }
        
        /* Show action buttons on hover with smooth animation - only for canvas elements */
        .general_canva [class*="_block"]:hover .duplicate-btn,
        .general_canva [class*="_block"]:hover .quick-delete-btn {
            display: flex;
            animation: fadeInScale 0.2s ease-out;
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Simplified inline editing styles */
        .inline-editing {
            outline: 2px solid #007bff !important;
            outline-offset: 2px;
            background: rgba(0, 123, 255, 0.1) !important;
            border-radius: 4px !important;
            position: relative !important;
        }
        
        .inline-editing:focus {
            outline: 2px solid #0056b3 !important;
            background: rgba(0, 86, 179, 0.15) !important;
        }
        
        /* Edit completion feedback */
        .edit-completed {
            animation: editSuccess 1s ease-out;
        }
        
        @keyframes editSuccess {
            0% {
                background: rgba(40, 167, 69, 0.2);
                transform: scale(1.02);
            }
            100% {
                background: transparent;
                transform: scale(1);
            }
        }
        
        /* Enhanced element selection styles */
        .edit_mode {
            outline: none !important;
            background: rgba(0, 123, 255, 0.05) !important;
            position: relative;
        }
        
        .edit_mode::before {
            display: none;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Block hover effects - only for non-text elements */
        .general_canva [class*="_block"]:hover:not(.edit_mode):not(.inline-editing) {
            outline: 1px dashed rgba(0, 123, 255, 0.3);
            outline-offset: 1px;
        }
        
        /* Inline-editable elements hover indication */
        .general_canva .el_text:hover,
        .general_canva .el_button:hover,
        .general_canva .el_link:hover,
        .general_canva .product_title:hover,
        .general_canva .product_description:hover,
        .general_canva .product_badge:hover,
        .general_canva .product_price:hover,
        .general_canva .product_old_price:hover,
        .general_canva .product_discount:hover,
        .general_canva .product_buy_btn:hover {
            outline: 2px solid #007bff !important;
            outline-offset: 1px;
            cursor: text !important;
            position: relative;
        }
        
        .general_canva .el_text:hover::after,
        .general_canva .el_button:hover::after,
        .general_canva .el_link:hover::after,
        .general_canva .product_title:hover::after,
        .general_canva .product_description:hover::after,
        .general_canva .product_badge:hover::after,
        .general_canva .product_price:hover::after,
        .general_canva .product_old_price:hover::after,
        .general_canva .product_discount:hover::after,
        .general_canva .product_buy_btn:hover::after {
            content: "✏️ Двойной клик";
            position: absolute;
            top: -25px;
            left: 0;
            background: #007bff;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            white-space: nowrap;
            z-index: 1000;
            pointer-events: none;
        }
    `);

// Duplicate functionality
function addDuplicateButtons() {
    // Remove all existing action buttons first
    $('.duplicate-btn, .quick-delete-btn').remove();
    
    // Add action buttons only to main element containers INSIDE canvas
    $('.general_canva > [class*="_block"]').each(function() {
        const $element = $(this);
        
        // Double check that element is direct child of canvas
        if (!$element.parent().hasClass('general_canva')) return;
        
        // Add duplicate button
        const $duplicateBtn = $('<button class="duplicate-btn" title="Дублировать элемент">⧉</button>');
        $element.css('position', 'relative').prepend($duplicateBtn);
        
        $duplicateBtn.off('click').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            duplicateElement($element);
        });

        // Add quick delete button
        const $deleteBtn = $('<button class="quick-delete-btn" title="Быстро удалить элемент">✕</button>');
        $element.prepend($deleteBtn);
        
        $deleteBtn.off('click').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            quickDeleteElement($element);
        });
    });
}

function duplicateElement($element) {
    const $clone = $element.clone(true);
    
    // Update element counter and data-id - access global elementCounter
    if (typeof window.elementCounter !== 'undefined') {
        window.elementCounter++;
        $clone.attr('data-id', window.elementCounter);
    } else {
        // Fallback: find highest existing data-id and increment
        let maxId = 0;
        $('.general_canva [data-id]').each(function() {
            const id = parseInt($(this).attr('data-id')) || 0;
            if (id > maxId) maxId = id;
        });
        $clone.attr('data-id', maxId + 1);
    }
    
    // Remove any selection classes
    $clone.removeClass('edit_mode highlight-element');
    
    // Insert after original element
    $element.after($clone);
    
    // Re-add action buttons
    addDuplicateButtons();
    
    // Re-enable inline editing for new elements
    enableInlineEditing();
    
    // Mark project as changed
    if (window.markProjectAsChanged) {
        markProjectAsChanged();
    }
    
    // Show notification
    if (window.showNotification) {
        showNotification('Элемент продублирован!', 'success');
    }
}

// Quick delete functionality
function quickDeleteElement($element) {
    // Confirm deletion
    if (!confirm('Удалить этот элемент?')) {
        return;
    }
    
    // Remove the element with animation
    $element.addClass('deleting');
    $element.fadeOut(200, function() {
        $element.remove();
        
        // Re-add action buttons to remaining elements
        addDuplicateButtons();
        
        // Re-enable inline editing
        enableInlineEditing();
        
        // Mark project as changed
        if (window.markProjectAsChanged) {
            markProjectAsChanged();
        }
        
        // Show notification
        if (window.showNotification) {
            showNotification('Элемент удален!', 'success');
        }
    });
}

// Simplified inline editing functionality - only for canvas text elements
function enableInlineEditing() {
    console.log('🔧 Setting up inline editing...');
    
    // Only allow editing of text elements INSIDE the canvas
    const editableSelectors = [
        '.general_canva .el_text',
        '.general_canva .el_link',
        '.general_canva .product_title',
        '.general_canva .product_description',
        '.general_canva .product_badge',
        '.general_canva .product_price',
        '.general_canva .product_old_price',
        '.general_canva .product_discount',
        '.general_canva .product_buy_btn'
    ].join(', ');
    
    console.log('📋 Editable selectors:', editableSelectors);
    
    // Check if elements exist
    const foundElements = $(editableSelectors);
    console.log(`🔍 Found ${foundElements.length} editable elements in DOM`);
    foundElements.each(function(index) {
        console.log(`  ${index + 1}. ${this.tagName}.${this.className} - "${$(this).text().trim()}"`);
    });
    
    // Note: Button editing is now handled by the main dblclick handler below
    
    // Remove any existing event handlers to prevent duplicates
    $(document).off('dblclick.inline-edit');
    
    $(document).on('dblclick.inline-edit', editableSelectors, function(e) {
        console.log('🖱️ Double click detected on:', this.tagName + '.' + this.className, 'Text:', $(this).text().trim());
        
        // Check if double-click target is an action button
        if ($(e.target).hasClass('duplicate-btn') || $(e.target).hasClass('quick-delete-btn')) {
            console.log('❌ Skipping - action button clicked');
            return;
        }
        
        e.stopPropagation();
        e.preventDefault(); // Prevent any default double-click behavior
        
        const $element = $(this);
        
        // Clear any pending click timeouts to prevent inspector opening
        if (window.clickTimeout) {
            clearTimeout(window.clickTimeout);
            window.clickTimeout = null;
            console.log('🚫 Cleared pending inspector timeout');
        }
        
        // Double check that element is inside canvas
        if (!$element.closest('.general_canva').length) {
            console.log('❌ Skipping - element not in canvas');
            return;
        }
        
        // Skip if already editing or if it's an action button
        if ($element.hasClass('inline-editing') || $element.hasClass('duplicate-btn') || $element.hasClass('quick-delete-btn')) {
            console.log('❌ Skipping - already editing or action button');
            return;
        }
        
        // Skip if element has no text content
        if (!$element.text().trim()) {
            console.log('❌ Skipping - no text content');
            return;
        }
        
        // Close inspector if it's open
        if ($('.general_sidebar_right').hasClass('inspector-open')) {
            $('.general_sidebar_right').removeClass('inspector-open');
            $('#inspector').hide();
            console.log('📋 Closed inspector for inline editing');
        }
        
        console.log('✅ Starting inline editing for:', $element);
        startInlineEditing($element);
    });
    
    
    // Add mobile support for inline editing via long press
    let longPressTimer = null;
    let startTouch = null;
    
    $(document).on('touchstart', editableSelectors, function(e) {
        const $element = $(this);
        startTouch = {
            x: e.originalEvent.touches[0].clientX,
            y: e.originalEvent.touches[0].clientY
        };
        
        longPressTimer = setTimeout(() => {
            // Check if finger hasn't moved too much
            if (startTouch && !$element.hasClass('inline-editing')) {
                console.log('📱 Long press detected - starting inline editing');
                // Clear any pending inspector timeout
                if (window.clickTimeout) {
                    clearTimeout(window.clickTimeout);
                    window.clickTimeout = null;
                }
                startInlineEditing($element);
            }
        }, 800); // 800ms long press
    });
    
    $(document).on('touchmove', editableSelectors, function(e) {
        if (startTouch && longPressTimer) {
            const moveX = Math.abs(e.originalEvent.touches[0].clientX - startTouch.x);
            const moveY = Math.abs(e.originalEvent.touches[0].clientY - startTouch.y);
            
            // Cancel long press if finger moved too much
            if (moveX > 10 || moveY > 10) {
                clearTimeout(longPressTimer);
                longPressTimer = null;
            }
        }
    });
    
    $(document).on('touchend touchcancel', editableSelectors, function(e) {
        if (longPressTimer) {
            clearTimeout(longPressTimer);
            longPressTimer = null;
        }
        startTouch = null;
    });
    
    console.log('🎯 Inline editing setup completed. Event handlers bound (desktop + mobile).');
}

function startInlineEditing($element) {
    // Clear any pending inspector opening timeouts
    if (window.clickTimeout) {
        clearTimeout(window.clickTimeout);
        window.clickTimeout = null;
    }
    
    // Add editing class for visual feedback
    $element.addClass('inline-editing');
    
    // Store original content as text only
    const originalText = $element.text().trim();
    
    // Make element editable
    $element.attr('contenteditable', 'true');
    $element.focus();
    
    // Select all text for easy replacement
    selectElementText($element[0]);
    
    // Create finish editing function
    const finishEditing = function(e) {
        // Only finish on blur or Enter key
        if (e && e.type === 'keydown' && e.key !== 'Enter') return;
        if (e && e.type === 'keydown' && e.key === 'Enter') {
            e.preventDefault();
        }
        
        // Remove editing state
        $element.removeClass('inline-editing');
        $element.removeAttr('contenteditable');
        $element.off('.inline-edit');
        
        // Get new text content and clean it
        const newText = $element.text().trim();
        
        // Only update if text actually changed
        if (newText !== originalText && newText !== '') {
            $element.text(newText);
            
            // Mark project as changed
            if (window.markProjectAsChanged) {
                markProjectAsChanged();
            }
            
            // Show completion feedback
            $element.addClass('edit-completed');
            setTimeout(() => $element.removeClass('edit-completed'), 800);
        } else if (newText === '') {
            // Restore original text if empty
            $element.text(originalText);
        }
    };
    
    // Bind finish editing events
    $element.on('blur.inline-edit keydown.inline-edit', finishEditing);
    
    // Handle Escape key to cancel editing
    $element.on('keydown.inline-edit', function(e) {
        if (e.key === 'Escape') {
            $element.text(originalText);
            $element.removeClass('inline-editing');
            $element.removeAttr('contenteditable');
            $element.off('.inline-edit');
        }
    });
}

function selectElementText(element) {
    if (window.getSelection && document.createRange) {
        const range = document.createRange();
        const selection = window.getSelection();
        range.selectNodeContents(element);
        selection.removeAllRanges();
        selection.addRange(range);
    }
}

// Enhanced visual feedback functions
function showEditingHint() {
    if ($('.editing-hint').length > 0) return;
    
    const $hint = $(`
        <div class="editing-hint" style="
            position: fixed;
            top: 80px;
            right: 20px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            animation: slideInRight 0.3s ease-out;
        ">
            💡 Двойной клик для редактирования текста
        </div>
    `);
    
    $('body').append($hint);
    
    setTimeout(() => {
        $hint.fadeOut(300, () => $hint.remove());
    }, 3000);
}

// Product card functionality
function enableProductCardFeatures() {
    // Image upload functionality
    $(document).on('change', '.general_canva .product_image_input', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const $container = $(this).closest('.product_image_container');
        const $placeholder = $container.find('.product_image_placeholder');
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $placeholder.html(`<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`);
                if (window.markProjectAsChanged) {
                    markProjectAsChanged();
                }
            };
            reader.readAsDataURL(file);
        } else {
            alert('Пожалуйста, выберите файл изображения');
        }
    });
    
    // Button hover effects
    $(document).on('mouseenter', '.general_canva .product_buy_btn', function() {
        $(this).css('background', '#1565c0');
    }).on('mouseleave', '.general_canva .product_buy_btn', function() {
        $(this).css('background', '#1976d2');
    });
    
    $(document).on('mouseenter', '.general_canva .product_cart_btn, .general_canva .product_like_btn', function() {
        $(this).css('background', '#e0e0e0');
    }).on('mouseleave', '.general_canva .product_cart_btn, .general_canva .product_like_btn', function() {
        $(this).css('background', '#f5f5f5');
    });
    
    // Card hover effect
    $(document).on('mouseenter', '.general_canva .el_product_card', function() {
        $(this).css('transform', 'translateY(-4px)');
        $(this).css('box-shadow', '0 8px 24px rgba(0,0,0,0.15)');
    }).on('mouseleave', '.general_canva .el_product_card', function() {
        $(this).css('transform', 'translateY(0)');
        $(this).css('box-shadow', '0 4px 12px rgba(0,0,0,0.1)');
    });
}

// Smart hover effects to prioritize text over blocks
function enableSmartHoverEffects() {
    const textSelectors = [
        '.el_text', '.el_button', '.el_link',
        '.product_title', '.product_description', '.product_badge',
        '.product_price', '.product_old_price', '.product_discount', '.product_buy_btn'
    ];
    
    // When hovering over text elements, hide parent block outline
    $(document).on('mouseenter', textSelectors.map(s => `.general_canva ${s}`).join(', '), function() {
        const $block = $(this).closest('[class*="_block"]');
        $block.addClass('text-hovered');
    });
    
    $(document).on('mouseleave', textSelectors.map(s => `.general_canva ${s}`).join(', '), function() {
        const $block = $(this).closest('[class*="_block"]');
        $block.removeClass('text-hovered');
    });
    
    // Add CSS rule to hide block outline when text is hovered
    $('<style>').appendTo('head').text(`
        .general_canva [class*="_block"].text-hovered {
            outline: none !important;
        }
    `);
}

function addKeyboardShortcuts() {
    $(document).on('keydown', function(e) {
        // Only work with canvas elements
        const $selected = $('.general_canva .edit_mode').first();
        
        // Ctrl/Cmd + D to duplicate selected element
        if ((e.ctrlKey || e.metaKey) && e.key === 'd' && $selected.length > 0) {
            e.preventDefault();
            duplicateElement($selected);
        }
        
        // Delete key to remove selected element
        if (e.key === 'Delete' && $selected.length > 0) {
            e.preventDefault();
            if (confirm('Удалить выбранный элемент?')) {
                $selected.remove();
                if (window.markProjectAsChanged) {
                    markProjectAsChanged();
                }
            }
        }
    });
}

// Initialize enhanced features
$(document).ready(function() {
    console.log('🚀 Initializing enhanced features...');
    
    // Enable inline editing first
    enableInlineEditing();
    console.log('✅ Inline editing enabled');
    
    // Add minimum touch target sizes for mobile
    if (window.matchMedia('(max-width: 768px)').matches) {
        $('.general_sidebar_nav_li, .general_add_button, .general_view').css({
            'min-height': '44px',
            'min-width': '44px'
        });
        console.log('📱 Mobile touch targets configured');
    }
    
    // Enable product card functionality
    enableProductCardFeatures();
    
    // Enable smart hover effects
    enableSmartHoverEffects();
    
    // Add keyboard shortcuts
    addKeyboardShortcuts();
    
    // Add duplicate buttons to existing elements with delay
    setTimeout(() => {
        addDuplicateButtons();
        console.log('Duplicate buttons initialized');
    }, 300);
    
    // Hook into element addition with debouncing
    let addElementTimeout;
    const originalAddElementToCanvas = window.addElementToCanvas;
    if (originalAddElementToCanvas) {
        window.addElementToCanvas = function(...args) {
            const result = originalAddElementToCanvas.apply(this, args);
            
            // Debounce duplicate button addition
            clearTimeout(addElementTimeout);
            addElementTimeout = setTimeout(() => {
                addDuplicateButtons();
                
                // Show hint for new users (only once)
                if (localStorage.getItem('editing-hint-shown') !== 'true') {
                    showEditingHint();
                    localStorage.setItem('editing-hint-shown', 'true');
                }
            }, 150);
            
            return result;
        };
    }
    
    // Add CSS animations only once
    if (!$('#enhanced-animations').length) {
        $('<style id="enhanced-animations">').appendTo('head').text(`
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
        `);
    }
});

