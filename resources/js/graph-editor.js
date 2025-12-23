// Graph Editor - Drag and Drop Functionality
class GraphEditor {
    constructor() {
        this.canvas = document.getElementById('canvas');
        this.canvasContent = this.canvas.querySelector('.relative');
        this.components = [];
        this.connections = [];
        this.draggedElement = null;
        this.isDraggingCanvas = false;
        this.canvasOffset = { x: 0, y: 0 };
        this.startDragPos = { x: 0, y: 0 };
        this.scale = 1;
        this.componentCounter = 0;
        this.connectionMode = false;
        this.connectionStart = null;
        this.tempLine = null;

        this.init();
    }

    init() {
        this.setupSidebarDragAndDrop();
        this.setupCanvasDragAndDrop();
        this.setupCanvasPanning();
        this.setupZoomControls();
        this.setupExistingComponents();
        this.setupConnectionCanvas();
        this.setupConnectionMode();
        this.setupClearAll();
        this.setupJSONEditorModal();
    }

    // Setup drag from sidebar
    setupSidebarDragAndDrop() {
        const sidebarComponents = document.querySelectorAll('[data-component-type]');

        sidebarComponents.forEach(component => {
            component.addEventListener('dragstart', (e) => {
                const componentType = e.target.closest('[data-component-type]').dataset.componentType;
                e.dataTransfer.setData('componentType', componentType);
                e.dataTransfer.effectAllowed = 'copy';
            });
        });
    }

    // Setup canvas drop zone
    setupCanvasDragAndDrop() {
        this.canvas.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        });

        this.canvas.addEventListener('drop', (e) => {
            e.preventDefault();
            const componentType = e.dataTransfer.getData('componentType');

            if (componentType) {
                const rect = this.canvas.getBoundingClientRect();
                const x = (e.clientX - rect.left + this.canvas.scrollLeft) / this.scale;
                const y = (e.clientY - rect.top + this.canvas.scrollTop) / this.scale;

                this.createComponent(componentType, x, y);
            }
        });
    }

    // Create a new component on the canvas
    createComponent(type, x, y) {
        this.componentCounter++;
        const componentId = `${type}-${this.componentCounter}`;

        const componentConfig = this.getComponentConfig(type);

        const componentEl = document.createElement('div');
        componentEl.className = 'absolute bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 w-48 cursor-move hover:shadow-xl transition-shadow';
        componentEl.style.left = `${x}px`;
        componentEl.style.top = `${y}px`;
        componentEl.dataset.componentId = componentId;
        componentEl.draggable = true;

        // Special handling for Switch component
        if (type === 'switch') {
            componentEl.innerHTML = this.createSwitchHTML(componentId, componentConfig);
        } else {
            componentEl.innerHTML = `
                <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                    <div class="w-6 h-6 ${componentConfig.bgColor} rounded flex items-center justify-center text-white text-xs font-bold">
                        ${componentConfig.icon}
                    </div>
                    <span class="text-sm font-medium text-slate-800 dark:text-slate-100">${componentConfig.name}${this.componentCounter}</span>
                    <button class="ml-auto text-slate-400 hover:text-red-600 dark:hover:text-red-400 delete-btn" data-component-id="${componentId}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="px-4 py-3">
                    <button class="configure-btn text-sm text-blue-600 dark:text-blue-400 hover:underline" data-component-id="${componentId}">Configure</button>
                </div>
            `;
        }

        this.canvasContent.appendChild(componentEl);

        // Add connection dots (regular components get 4 dots, switch gets input dots only)
        if (type === 'switch') {
            this.addSwitchInputDots(componentEl);
        } else {
            this.addConnectionDots(componentEl);
        }

        this.setupComponentDragging(componentEl);
        this.setupComponentDeletion(componentEl);

        const component = {
            id: componentId,
            type: type,
            element: componentEl,
            x: x,
            y: y
        };

        // Initialize switch-specific data
        if (type === 'switch') {
            component.conditions = [
                { type: 'if', expression: '', id: 'cond-1' }
            ];
            this.setupSwitchHandlers(componentEl, component);
        }

        this.components.push(component);
    }

    // Add input connection dots for switch component
    addSwitchInputDots(componentEl) {
        const positions = ['left', 'top', 'bottom'];

        positions.forEach(pos => {
            const dot = document.createElement('div');
            dot.className = `connection-dot ${pos}`;
            dot.dataset.position = pos;
            dot.dataset.componentId = componentEl.dataset.componentId;

            dot.addEventListener('mousedown', (e) => {
                e.stopPropagation();
                e.preventDefault();
                this.startConnection(componentEl.dataset.componentId, pos, e);
            });

            componentEl.appendChild(dot);
        });
    }

    // Create HTML for Switch component
    createSwitchHTML(componentId, componentConfig) {
        return `
            <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <div class="w-6 h-6 ${componentConfig.bgColor} rounded flex items-center justify-center text-white text-xs font-bold">
                    ${componentConfig.icon}
                </div>
                <span class="text-sm font-medium text-slate-800 dark:text-slate-100">${componentConfig.name}</span>
                <button class="ml-auto text-slate-400 hover:text-red-600 dark:hover:text-red-400 delete-btn" data-component-id="${componentId}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="switch-conditions px-3 py-2 space-y-2" data-component-id="${componentId}">
                <div class="condition-item bg-slate-50 dark:bg-slate-900/50 rounded p-2 border border-slate-200 dark:border-slate-700" data-condition-id="cond-1">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">If</span>
                        <div class="connection-dot-switch right" data-condition-id="cond-1"></div>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono bg-white dark:bg-slate-800 px-2 py-1 rounded border border-slate-200 dark:border-slate-700">
                        <span class="condition-expression">Click to edit</span>
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-t border-slate-200 dark:border-slate-700">
                <button class="add-condition-btn text-xs text-blue-600 dark:text-blue-400 hover:underline" data-component-id="${componentId}">
                    + Add Condition
                </button>
            </div>
        `;
    }

    // Get component configuration
    getComponentConfig(type) {
        const configs = {
            'request': { name: 'request', icon: 'R', bgColor: 'bg-slate-500' },
            'response': { name: 'response', icon: 'R', bgColor: 'bg-emerald-500' },
            'decision-table': { name: 'decisionTable', icon: 'DT', bgColor: 'bg-blue-500' },
            'expression': { name: 'expression', icon: 'E', bgColor: 'bg-purple-500' },
            'function': { name: 'function', icon: 'F', bgColor: 'bg-orange-500' },
            'switch': { name: 'switch', icon: 'S', bgColor: 'bg-violet-500' },
            'decision': { name: 'decision', icon: 'D', bgColor: 'bg-slate-500' }
        };

        return configs[type] || configs['request'];
    }

    // Setup dragging for existing and new components
    setupExistingComponents() {
        const existingComponents = this.canvasContent.querySelectorAll('[data-component-id]');
        existingComponents.forEach(component => {
            this.addConnectionDots(component);
            this.setupComponentDragging(component);
            this.setupComponentDeletion(component);

            // Add to components array
            const componentId = component.dataset.componentId;
            if (!this.components.find(c => c.id === componentId)) {
                this.components.push({
                    id: componentId,
                    type: componentId.split('-')[0],
                    element: component,
                    x: 0,
                    y: 0
                });
            }
        });
    }

    // Add connection dots to a component
    addConnectionDots(componentEl) {
        // Check if dots already exist
        if (componentEl.querySelector('.connection-dot')) {
            return; // Dots already added
        }

        // Check if this is a switch component - they have their own connection dots per condition
        const componentId = componentEl.dataset.componentId;
        if (componentId && componentId.startsWith('switch-')) {
            return; // Switch components handle their own connection dots
        }

        const positions = ['right', 'left', 'top', 'bottom'];

        positions.forEach(pos => {
            const dot = document.createElement('div');
            dot.className = `connection-dot ${pos}`;
            dot.dataset.position = pos;
            dot.dataset.componentId = componentEl.dataset.componentId;

            dot.addEventListener('mousedown', (e) => {
                e.stopPropagation();
                e.preventDefault();
                this.startConnection(componentEl.dataset.componentId, pos, e);
            });

            componentEl.appendChild(dot);
        });
    }

    // Start creating a connection
    startConnection(componentId, position, e) {
        e.preventDefault();
        e.stopPropagation();

        console.log('Starting connection from:', componentId, position);

        // Clean up any existing connection attempt
        if (this.connectionStart) {
            this.cleanupConnection(null);
        }

        this.connectionStart = { componentId, position };

        // Highlight the starting dot
        const startDot = e.target;
        startDot.classList.add('active');

        // Create temporary line
        this.createTempLine(e);

        // Store handlers so we can remove them later
        this.currentConnectionHandlers = {
            mouseMoveHandler: (e) => {
                this.updateTempLine(e);
            },
            mouseUpHandler: (e) => {
                // Check if we released on a connection dot
                const targetElement = document.elementFromPoint(e.clientX, e.clientY);

                console.log('Mouse up on:', targetElement);

                if (targetElement && targetElement.classList.contains('connection-dot')) {
                    const targetComponentId = targetElement.dataset.componentId;
                    const targetPosition = targetElement.dataset.position;

                    console.log('Target dot:', targetComponentId, targetPosition);

                    // Only create connection if it's a different component
                    if (targetComponentId !== componentId) {
                        console.log('Creating connection!');
                        this.createConnection(
                            this.connectionStart.componentId,
                            targetComponentId,
                            this.connectionStart.position,
                            targetPosition
                        );
                    } else {
                        console.log('Same component, not connecting');
                    }
                } else {
                    console.log('Not released on a dot');
                }

                // Cleanup
                this.cleanupConnection(startDot);
            }
        };

        document.addEventListener('mousemove', this.currentConnectionHandlers.mouseMoveHandler);
        document.addEventListener('mouseup', this.currentConnectionHandlers.mouseUpHandler);
    }

    // Cleanup connection state
    cleanupConnection(startDot) {
        console.log('Cleaning up connection');

        if (this.currentConnectionHandlers) {
            document.removeEventListener('mousemove', this.currentConnectionHandlers.mouseMoveHandler);
            document.removeEventListener('mouseup', this.currentConnectionHandlers.mouseUpHandler);
            this.currentConnectionHandlers = null;
        }

        if (this.tempLine) {
            this.tempLine.remove();
            this.tempLine = null;
        }

        if (startDot) {
            startDot.classList.remove('active');
        }

        // Remove active class from all dots
        const allDots = this.canvasContent.querySelectorAll('.connection-dot.active');
        allDots.forEach(dot => dot.classList.remove('active'));

        this.connectionStart = null;

        console.log('Connection cleaned up, ready for next connection');
    }

    // Create temporary line while dragging
    createTempLine(e) {
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('stroke', '#94a3b8');
        line.setAttribute('stroke-width', '2');
        line.setAttribute('stroke-dasharray', '5,5');
        this.tempLine = line;
        this.connectionCanvas.appendChild(line);
        this.updateTempLine(e);
    }

    // Update temporary line position
    updateTempLine(e) {
        if (!this.tempLine || !this.connectionStart) return;

        const startComponent = this.canvasContent.querySelector(`[data-component-id="${this.connectionStart.componentId}"]`);
        if (!startComponent) return;

        const startRect = startComponent.getBoundingClientRect();
        const canvasRect = this.canvas.getBoundingClientRect();

        let startX, startY;
        const pos = this.connectionStart.position;

        if (pos === 'right') {
            startX = (startRect.right - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            startY = (startRect.top + startRect.height / 2 - canvasRect.top + this.canvas.scrollTop) / this.scale;
        } else if (pos === 'left') {
            startX = (startRect.left - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            startY = (startRect.top + startRect.height / 2 - canvasRect.top + this.canvas.scrollTop) / this.scale;
        } else if (pos === 'top') {
            startX = (startRect.left + startRect.width / 2 - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            startY = (startRect.top - canvasRect.top + this.canvas.scrollTop) / this.scale;
        } else { // bottom
            startX = (startRect.left + startRect.width / 2 - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            startY = (startRect.bottom - canvasRect.top + this.canvas.scrollTop) / this.scale;
        }

        const endX = (e.clientX - canvasRect.left + this.canvas.scrollLeft) / this.scale;
        const endY = (e.clientY - canvasRect.top + this.canvas.scrollTop) / this.scale;

        this.tempLine.setAttribute('x1', startX);
        this.tempLine.setAttribute('y1', startY);
        this.tempLine.setAttribute('x2', endX);
        this.tempLine.setAttribute('y2', endY);
    }

    setupComponentDragging(componentEl) {
        let isDragging = false;
        let startX, startY, initialLeft, initialTop;

        componentEl.addEventListener('mousedown', (e) => {
            if (e.target.closest('.delete-btn')) return;

            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;

            const rect = componentEl.getBoundingClientRect();
            const canvasRect = this.canvas.getBoundingClientRect();
            initialLeft = (rect.left - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            initialTop = (rect.top - canvasRect.top + this.canvas.scrollTop) / this.scale;

            componentEl.style.cursor = 'grabbing';
            e.preventDefault();
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            const deltaX = (e.clientX - startX) / this.scale;
            const deltaY = (e.clientY - startY) / this.scale;

            componentEl.style.left = `${initialLeft + deltaX}px`;
            componentEl.style.top = `${initialTop + deltaY}px`;

            // Redraw connections while dragging
            this.redrawConnections();
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                componentEl.style.cursor = 'move';
            }
        });
    }

    // Setup component deletion
    setupComponentDeletion(componentEl) {
        const deleteBtn = componentEl.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const componentId = deleteBtn.dataset.componentId;
                this.deleteComponent(componentId);
            });
        }
    }

    deleteComponent(componentId) {
        const component = this.components.find(c => c.id === componentId);
        if (component) {
            component.element.remove();
            this.components = this.components.filter(c => c.id !== componentId);
        }
    }

    // Canvas panning
    setupCanvasPanning() {
        let isPanning = false;
        let startX, startY, scrollLeft, scrollTop;

        this.canvas.addEventListener('mousedown', (e) => {
            // Only pan if clicking on the canvas background, not on components
            if (e.target === this.canvas || e.target === this.canvasContent) {
                isPanning = true;
                startX = e.pageX - this.canvas.offsetLeft;
                startY = e.pageY - this.canvas.offsetTop;
                scrollLeft = this.canvas.scrollLeft;
                scrollTop = this.canvas.scrollTop;
                this.canvas.style.cursor = 'grabbing';
            }
        });

        this.canvas.addEventListener('mousemove', (e) => {
            if (!isPanning) return;
            e.preventDefault();

            const x = e.pageX - this.canvas.offsetLeft;
            const y = e.pageY - this.canvas.offsetTop;
            const walkX = (x - startX);
            const walkY = (y - startY);

            this.canvas.scrollLeft = scrollLeft - walkX;
            this.canvas.scrollTop = scrollTop - walkY;
        });

        this.canvas.addEventListener('mouseup', () => {
            isPanning = false;
            this.canvas.style.cursor = 'default';
        });

        this.canvas.addEventListener('mouseleave', () => {
            isPanning = false;
            this.canvas.style.cursor = 'default';
        });
    }

    // Zoom controls
    setupZoomControls() {
        const zoomInBtn = document.getElementById('zoom-in');
        const zoomOutBtn = document.getElementById('zoom-out');
        const fitScreenBtn = document.getElementById('fit-screen');
        const resetBtn = document.getElementById('reset-view');

        if (zoomInBtn) {
            zoomInBtn.addEventListener('click', () => this.zoom(0.1));
        }

        if (zoomOutBtn) {
            zoomOutBtn.addEventListener('click', () => this.zoom(-0.1));
        }

        if (fitScreenBtn) {
            fitScreenBtn.addEventListener('click', () => this.fitToScreen());
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.resetView());
        }

        // Mouse wheel zoom
        this.canvas.addEventListener('wheel', (e) => {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                const delta = e.deltaY > 0 ? -0.05 : 0.05;
                this.zoom(delta);
            }
        }, { passive: false });
    }

    zoom(delta) {
        const newScale = Math.max(0.1, Math.min(3, this.scale + delta));
        this.scale = newScale;
        this.canvasContent.style.transform = `scale(${this.scale})`;
        this.canvasContent.style.transformOrigin = 'top left';
    }

    fitToScreen() {
        if (this.components.length === 0) {
            this.resetView();
            return;
        }

        // Calculate bounding box of all components
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

        this.components.forEach(comp => {
            const rect = comp.element.getBoundingClientRect();
            const canvasRect = this.canvas.getBoundingClientRect();
            const x = (rect.left - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            const y = (rect.top - canvasRect.top + this.canvas.scrollTop) / this.scale;

            minX = Math.min(minX, x);
            minY = Math.min(minY, y);
            maxX = Math.max(maxX, x + rect.width / this.scale);
            maxY = Math.max(maxY, y + rect.height / this.scale);
        });

        const padding = 50;
        const contentWidth = maxX - minX + padding * 2;
        const contentHeight = maxY - minY + padding * 2;

        const scaleX = this.canvas.clientWidth / contentWidth;
        const scaleY = this.canvas.clientHeight / contentHeight;

        this.scale = Math.min(scaleX, scaleY, 1);
        this.canvasContent.style.transform = `scale(${this.scale})`;
        this.canvasContent.style.transformOrigin = 'top left';

        this.canvas.scrollLeft = (minX - padding) * this.scale;
        this.canvas.scrollTop = (minY - padding) * this.scale;
    }

    resetView() {
        this.scale = 1;
        this.canvasContent.style.transform = 'scale(1)';
        this.canvas.scrollLeft = 0;
        this.canvas.scrollTop = 0;
    }

    // Setup SVG canvas for connections
    setupConnectionCanvas() {
        // Create SVG element for drawing connections
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.id = 'connection-canvas';
        svg.style.position = 'absolute';
        svg.style.top = '0';
        svg.style.left = '0';
        svg.style.width = '100%';
        svg.style.height = '100%';
        svg.style.pointerEvents = 'none';
        svg.style.zIndex = '1';

        this.canvasContent.insertBefore(svg, this.canvasContent.firstChild);
        this.connectionCanvas = svg;
    }

    // Setup connection mode toggle (removed - no longer needed)
    setupConnectionMode() {
        // Connection mode is now handled by dots
    }

    // Create a connection between two components
    createConnection(fromId, toId, fromPos = 'right', toPos = 'left') {
        const connection = {
            id: `conn-${Date.now()}`,
            from: fromId,
            to: toId,
            fromPos: fromPos,
            toPos: toPos
        };

        this.connections.push(connection);
        this.drawConnection(connection);
    }

    // Draw a connection line
    drawConnection(connection) {
        const fromEl = this.canvasContent.querySelector(`[data-component-id="${connection.from}"]`);
        const toEl = this.canvasContent.querySelector(`[data-component-id="${connection.to}"]`);

        if (!fromEl || !toEl) return;

        const line = this.createConnectionLine(fromEl, toEl, connection.id, connection);
        this.connectionCanvas.appendChild(line);
    }

    // Create SVG line element
    createConnectionLine(fromEl, toEl, connectionId, connection) {
        const fromRect = fromEl.getBoundingClientRect();
        const toRect = toEl.getBoundingClientRect();
        const canvasRect = this.canvas.getBoundingClientRect();

        // Calculate start point based on fromPos
        let fromX, fromY;
        const fromPos = connection.fromPos || 'right';

        if (fromPos === 'right') {
            fromX = (fromRect.right - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            fromY = (fromRect.top + fromRect.height / 2 - canvasRect.top + this.canvas.scrollTop) / this.scale;
        } else if (fromPos === 'left') {
            fromX = (fromRect.left - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            fromY = (fromRect.top + fromRect.height / 2 - canvasRect.top + this.canvas.scrollTop) / this.scale;
        } else if (fromPos === 'top') {
            fromX = (fromRect.left + fromRect.width / 2 - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            fromY = (fromRect.top - canvasRect.top + this.canvas.scrollTop) / this.scale;
        } else { // bottom
            fromX = (fromRect.left + fromRect.width / 2 - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            fromY = (fromRect.bottom - canvasRect.top + this.canvas.scrollTop) / this.scale;
        }

        // Calculate end point based on toPos
        let toX, toY;
        const toPos = connection.toPos || 'left';

        if (toPos === 'right') {
            toX = (toRect.right - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            toY = (toRect.top + toRect.height / 2 - canvasRect.top + this.canvas.scrollTop) / this.scale;
        } else if (toPos === 'left') {
            toX = (toRect.left - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            toY = (toRect.top + toRect.height / 2 - canvasRect.top + this.canvas.scrollTop) / this.scale;
        } else if (toPos === 'top') {
            toX = (toRect.left + toRect.width / 2 - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            toY = (toRect.top - canvasRect.top + this.canvas.scrollTop) / this.scale;
        } else { // bottom
            toX = (toRect.left + toRect.width / 2 - canvasRect.left + this.canvas.scrollLeft) / this.scale;
            toY = (toRect.bottom - canvasRect.top + this.canvas.scrollTop) / this.scale;
        }

        // Create path with curve
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

        // Calculate control points for smooth curve
        const dx = toX - fromX;
        const dy = toY - fromY;
        const distance = Math.sqrt(dx * dx + dy * dy);
        const offset = Math.min(distance / 2, 100);

        let cp1x = fromX, cp1y = fromY;
        let cp2x = toX, cp2y = toY;

        if (fromPos === 'right') cp1x += offset;
        else if (fromPos === 'left') cp1x -= offset;
        else if (fromPos === 'top') cp1y -= offset;
        else cp1y += offset;

        if (toPos === 'right') cp2x += offset;
        else if (toPos === 'left') cp2x -= offset;
        else if (toPos === 'top') cp2y -= offset;
        else cp2y += offset;

        const d = `M ${fromX} ${fromY} C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${toX} ${toY}`;

        path.setAttribute('d', d);
        path.setAttribute('stroke', '#94a3b8');
        path.setAttribute('stroke-width', '2');
        path.setAttribute('fill', 'none');
        path.setAttribute('data-connection-id', connectionId);
        path.style.pointerEvents = 'stroke';
        path.style.cursor = 'pointer';

        // Add arrowhead
        const marker = document.createElementNS('http://www.w3.org/2000/svg', 'marker');
        marker.setAttribute('id', `arrow-${connectionId}`);
        marker.setAttribute('markerWidth', '10');
        marker.setAttribute('markerHeight', '10');
        marker.setAttribute('refX', '9');
        marker.setAttribute('refY', '3');
        marker.setAttribute('orient', 'auto');
        marker.setAttribute('markerUnits', 'strokeWidth');

        const arrowPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        arrowPath.setAttribute('d', 'M0,0 L0,6 L9,3 z');
        arrowPath.setAttribute('fill', '#94a3b8');

        marker.appendChild(arrowPath);
        this.connectionCanvas.appendChild(marker);

        path.setAttribute('marker-end', `url(#arrow-${connectionId})`);

        // Add double-click to delete (no confirmation needed)
        path.addEventListener('dblclick', (e) => {
            e.stopPropagation();
            console.log('Double-clicked connection, deleting:', connectionId);
            this.deleteConnection(connectionId);
        });

        // Add hover effect
        path.addEventListener('mouseenter', () => {
            path.setAttribute('stroke', '#ef4444'); // Red on hover
            path.setAttribute('stroke-width', '3');
        });

        path.addEventListener('mouseleave', () => {
            path.setAttribute('stroke', '#94a3b8'); // Back to gray
            path.setAttribute('stroke-width', '2');
        });

        return path;
    }

    // Delete a connection
    deleteConnection(connectionId) {
        this.connections = this.connections.filter(c => c.id !== connectionId);
        const line = this.connectionCanvas.querySelector(`[data-connection-id="${connectionId}"]`);
        if (line) line.remove();
        const marker = this.connectionCanvas.querySelector(`#arrow-${connectionId}`);
        if (marker) marker.remove();
    }

    // Redraw all connections (called when components move)
    redrawConnections() {
        // Clear all lines
        const lines = this.connectionCanvas.querySelectorAll('path[data-connection-id]');
        lines.forEach(line => line.remove());
        const markers = this.connectionCanvas.querySelectorAll('marker');
        markers.forEach(marker => marker.remove());

        // Redraw
        this.connections.forEach(conn => this.drawConnection(conn));
    }

    // Setup clear all button
    setupClearAll() {
        const clearBtn = document.getElementById('clear-all');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to delete all components and connections?')) {
                    this.clearAll();
                }
            });
        }
    }

    // Clear all components and connections
    clearAll() {
        // Remove all components
        this.components.forEach(comp => {
            comp.element.remove();
        });
        this.components = [];

        // Remove all connections
        this.connections = [];
        const lines = this.connectionCanvas.querySelectorAll('path[data-connection-id]');
        lines.forEach(line => line.remove());
        const markers = this.connectionCanvas.querySelectorAll('marker');
        markers.forEach(marker => marker.remove());

        // Reset counter
        this.componentCounter = 0;
    }

    // Setup JSON Editor Modal
    setupJSONEditorModal() {
        this.modal = document.getElementById('json-editor-modal');
        this.jsonEditor = document.getElementById('json-editor');
        this.lineNumbers = document.getElementById('line-numbers');
        this.jsonError = document.getElementById('json-error');
        this.currentEditingComponent = null;

        // Close modal buttons
        document.getElementById('close-modal').addEventListener('click', () => this.closeModal());
        document.getElementById('cancel-modal').addEventListener('click', () => this.closeModal());

        // Click outside to close
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.closeModal();
            }
        });

        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tab = e.target.dataset.tab;
                this.switchTab(tab);
            });
        });

        // JSON editor input
        this.jsonEditor.addEventListener('input', () => {
            this.updateLineNumbers();
            this.validateJSON();
        });

        // Save button
        document.getElementById('save-json').addEventListener('click', () => {
            this.saveJSON();
        });

        // Setup configure buttons for all components
        this.setupConfigureButtons();

        // Setup Switch Modal
        this.setupSwitchModal();

        // Setup Expression Modal
        this.setupExpressionModal();
    }

    // Setup Switch Modal
    setupSwitchModal() {
        this.switchModal = document.getElementById('switch-editor-modal');
        this.switchConditionsEditor = document.getElementById('switch-conditions-editor');

        // Close modal buttons
        document.getElementById('close-switch-modal').addEventListener('click', () => this.closeSwitchModal());
        document.getElementById('cancel-switch-modal').addEventListener('click', () => this.closeSwitchModal());

        // Click outside to close
        this.switchModal.addEventListener('click', (e) => {
            if (e.target === this.switchModal) {
                this.closeSwitchModal();
            }
        });

        // Add condition buttons
        document.getElementById('add-switch-condition').addEventListener('click', () => {
            this.addSwitchConditionInModal('elseif');
        });

        document.getElementById('add-switch-else').addEventListener('click', () => {
            this.addSwitchConditionInModal('else');
        });

        // Save button
        document.getElementById('save-switch').addEventListener('click', () => {
            this.saveSwitchConfiguration();
        });
    }

    // Setup configure buttons
    setupConfigureButtons() {
        // Use event delegation for dynamically added components
        this.canvasContent.addEventListener('click', (e) => {
            if (e.target.classList.contains('configure-btn') || e.target.closest('.configure-btn')) {
                const btn = e.target.classList.contains('configure-btn') ? e.target : e.target.closest('.configure-btn');
                const componentId = btn.dataset.componentId;
                this.openModal(componentId);
            }
        });
    }

    // Open modal for a component
    openModal(componentId) {
        const component = this.components.find(c => c.id === componentId);
        if (!component) return;

        // If it's a switch component, open switch modal instead
        if (component.type === 'switch') {
            this.openSwitchModal(component);
            return;
        }

        // If it's an expression component, open expression modal instead
        if (component.type === 'expression') {
            this.openExpressionModal(component);
            return;
        }

        this.currentEditingComponent = component;

        // Set modal title
        document.getElementById('modal-component-name').textContent = component.id;

        // Load existing JSON data or default based on component type
        let defaultData = '{\n  "country": "US",\n  "fee": 0\n}';

        if (component.type === 'request') {
            defaultData = '{\n  "country": "US",\n  "fee": 0\n}';
        } else if (component.type === 'response') {
            defaultData = '{\n  "status": "success",\n  "message": "OK"\n}';
        }

        const jsonData = component.jsonData || defaultData;
        this.jsonEditor.value = jsonData;

        // Update line numbers
        this.updateLineNumbers();

        // Show modal
        this.modal.classList.remove('hidden');

        // Focus editor
        setTimeout(() => this.jsonEditor.focus(), 100);
    }

    // Close modal
    closeModal() {
        this.modal.classList.add('hidden');
        this.currentEditingComponent = null;
        this.jsonError.classList.add('hidden');
    }

    // Switch tabs
    switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            if (btn.dataset.tab === tabName) {
                btn.classList.add('active', 'text-blue-600', 'dark:text-blue-400', 'border-blue-600', 'dark:border-blue-400');
                btn.classList.remove('text-slate-600', 'dark:text-slate-400');
            } else {
                btn.classList.remove('active', 'text-blue-600', 'dark:text-blue-400', 'border-blue-600', 'dark:border-blue-400');
                btn.classList.add('text-slate-600', 'dark:text-slate-400');
            }
        });

        // Show/hide tab content
        if (tabName === 'schema') {
            document.getElementById('schema-tab').classList.remove('hidden');
            document.getElementById('graph-tab').classList.add('hidden');
        } else {
            document.getElementById('schema-tab').classList.add('hidden');
            document.getElementById('graph-tab').classList.remove('hidden');
        }
    }

    // Update line numbers
    updateLineNumbers() {
        const lines = this.jsonEditor.value.split('\n').length;
        let lineNumbersHTML = '';
        for (let i = 1; i <= lines; i++) {
            lineNumbersHTML += i + '<br>';
        }
        this.lineNumbers.innerHTML = lineNumbersHTML;
    }

    // Validate JSON
    validateJSON() {
        try {
            const value = this.jsonEditor.value.trim();
            if (value) {
                JSON.parse(value);
            }
            this.jsonError.classList.add('hidden');
            return true;
        } catch (e) {
            this.jsonError.textContent = `Invalid JSON: ${e.message}`;
            this.jsonError.classList.remove('hidden');
            return false;
        }
    }

    // Save JSON
    saveJSON() {
        if (!this.validateJSON()) {
            return;
        }

        if (this.currentEditingComponent) {
            // Save JSON data to component
            this.currentEditingComponent.jsonData = this.jsonEditor.value;

            console.log('Saved JSON for', this.currentEditingComponent.id, ':', this.currentEditingComponent.jsonData);

            // Close modal
            this.closeModal();
        }
    }

    // Setup Switch component handlers
    setupSwitchHandlers(componentEl, component) {
        // Add condition button
        const addConditionBtn = componentEl.querySelector('.add-condition-btn');
        if (addConditionBtn) {
            addConditionBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.openSwitchModal(component);
            });
        }

        // Condition click to edit
        componentEl.addEventListener('click', (e) => {
            const conditionItem = e.target.closest('.condition-item');
            if (conditionItem && !e.target.closest('.connection-dot-switch')) {
                e.stopPropagation();
                this.openSwitchModal(component);
            }
        });
    }

    // Add a new condition to switch
    addSwitchCondition(component) {
        const conditions = component.conditions || [];
        const hasElse = conditions.some(c => c.type === 'else');

        if (hasElse) {
            alert('Cannot add more conditions after "Else"');
            return;
        }

        // Ask user what type of condition to add
        const options = conditions.length === 0
            ? ['If']
            : ['Else If', 'Else'];

        let conditionType;
        if (options.length === 1) {
            conditionType = options[0];
        } else {
            const choice = prompt(`Add condition type:\n1. Else If\n2. Else\n\nEnter 1 or 2:`, '1');
            if (choice === null) return;
            conditionType = choice === '2' ? 'Else' : 'Else If';
        }

        const conditionId = `cond-${Date.now()}`;
        const newCondition = {
            type: conditionType.toLowerCase().replace(' ', ''),
            expression: '',
            id: conditionId
        };

        conditions.push(newCondition);
        component.conditions = conditions;

        this.renderSwitchConditions(component);
    }

    // Render switch conditions
    renderSwitchConditions(component) {
        const conditionsContainer = component.element.querySelector('.switch-conditions');
        if (!conditionsContainer) return;

        conditionsContainer.innerHTML = '';

        component.conditions.forEach((condition, index) => {
            const conditionEl = document.createElement('div');
            conditionEl.className = 'condition-item bg-slate-50 dark:bg-slate-900/50 rounded p-2 border border-slate-200 dark:border-slate-700 relative cursor-pointer hover:border-blue-400';
            conditionEl.dataset.conditionId = condition.id;

            let label = condition.type === 'if' ? 'If' : condition.type === 'elseif' ? 'Else If' : 'Else';
            let showExpression = condition.type !== 'else';
            let expressionText = condition.expression || 'Click to configure';

            // Truncate long expressions
            if (expressionText.length > 30) {
                expressionText = expressionText.substring(0, 30) + '...';
            }

            conditionEl.innerHTML = `
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">${label}</span>
                </div>
                ${showExpression ? `
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono bg-white dark:bg-slate-800 px-2 py-1 rounded border border-slate-200 dark:border-slate-700">
                        <span class="condition-expression">${expressionText}</span>
                    </div>
                ` : `
                    <div class="text-xs text-slate-400 dark:text-slate-500 italic">Default case</div>
                `}
                <div class="connection-dot-switch right" data-condition-id="${condition.id}"></div>
            `;

            conditionsContainer.appendChild(conditionEl);
        });

        // Update connection dots
        this.updateSwitchConnectionDots(component);
    }

    // Remove a condition from switch
    removeSwitchCondition(component, conditionId) {
        component.conditions = component.conditions.filter(c => c.id !== conditionId);
        this.renderSwitchConditions(component);
    }

    // Edit switch condition
    editSwitchCondition(component, conditionId) {
        const condition = component.conditions.find(c => c.id === conditionId);
        if (!condition) return;

        if (condition.type === 'else') {
            alert('Else condition does not have an expression');
            return;
        }

        const expression = prompt(`Enter expression for ${condition.type === 'if' ? 'If' : 'Else If'}:`, condition.expression);
        if (expression !== null) {
            condition.expression = expression;
            this.renderSwitchConditions(component);
        }
    }

    // Update connection dots for switch
    updateSwitchConnectionDots(component) {
        const dots = component.element.querySelectorAll('.connection-dot-switch');
        console.log('Found connection dots:', dots.length);

        dots.forEach((dot, index) => {
            // Enhanced styling with better visibility - make it look like a regular connection dot
            dot.className = 'connection-dot connection-dot-switch right';

            // Set data attributes for connection system
            dot.dataset.position = 'right';
            dot.dataset.componentId = component.id;

            // Add tooltip
            const conditionId = dot.dataset.conditionId;
            const condition = component.conditions.find(c => c.id === conditionId);
            if (condition) {
                const label = condition.type === 'if' ? 'If' : condition.type === 'elseif' ? 'Else If' : 'Else';
                dot.title = `${label} output`;
            }

            // Add event listener directly (don't clone)
            dot.addEventListener('mousedown', (e) => {
                e.stopPropagation();
                e.preventDefault();

                console.log('Starting connection from switch condition:', component.id, conditionId);

                // Use the same connection system as regular components
                this.startConnection(component.id, 'right', e);

                // Store the condition ID for this connection
                if (this.connectionStart) {
                    this.connectionStart.conditionId = conditionId;
                }
            });
        });
    }

    // Open Switch Modal
    openSwitchModal(component) {
        this.currentEditingComponent = component;

        // Set modal title
        document.getElementById('switch-modal-title').textContent = `${component.id} - Configuration`;

        // Set switch mode
        const modeSelect = document.getElementById('switch-mode');
        modeSelect.value = component.switchMode || 'first';

        // Render conditions in modal
        this.renderSwitchConditionsInModal(component);

        // Show modal
        this.switchModal.classList.remove('hidden');
    }

    // Close Switch Modal
    closeSwitchModal() {
        this.switchModal.classList.add('hidden');
        this.currentEditingComponent = null;
    }

    // Render switch conditions in modal
    renderSwitchConditionsInModal(component) {
        this.switchConditionsEditor.innerHTML = '';

        if (!component.conditions || component.conditions.length === 0) {
            component.conditions = [{ type: 'if', expression: '', id: `cond-${Date.now()}` }];
        }

        component.conditions.forEach((condition, index) => {
            const conditionEl = document.createElement('div');
            conditionEl.className = 'bg-slate-50 dark:bg-slate-900/50 rounded-lg p-4 border border-slate-200 dark:border-slate-700';
            conditionEl.dataset.conditionId = condition.id;

            const label = condition.type === 'if' ? 'If' : condition.type === 'elseif' ? 'Else If' : 'Else';
            const showExpression = condition.type !== 'else';

            conditionEl.innerHTML = `
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">${label}</span>
                    ${index > 0 ? `
                        <button class="remove-condition-modal-btn text-sm text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300" data-condition-id="${condition.id}">
                            Remove
                        </button>
                    ` : ''}
                </div>
                ${showExpression ? `
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-2">Expression</label>
                        <div class="flex gap-2 mb-2">
                            <input
                                type="text"
                                class="condition-expression-input flex-1 px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-mono text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., company.type == 'LLC'"
                                value="${this.escapeHtml(condition.expression || '')}"
                                data-condition-id="${condition.id}"
                            />
                            <select class="operator-helper px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" data-condition-id="${condition.id}">
                                <option value="">+ Operator</option>
                                <option value=" == ">== (equals)</option>
                                <option value=" != ">!= (not equals)</option>
                                <option value=" > "> > (greater than)</option>
                                <option value=" < "> < (less than)</option>
                                <option value=" >= ">>= (greater or equal)</option>
                                <option value=" <= "><= (less or equal)</option>
                                <option value=" IN ">IN (contains)</option>
                                <option value=" NOT IN ">NOT IN (not contains)</option>
                                <option value=" && ">&& (and)</option>
                                <option value=" || ">|| (or)</option>
                            </select>
                        </div>
                        <div class="expression-preview text-xs font-mono p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded" data-condition-id="${condition.id}">
                            ${this.highlightExpression(condition.expression || 'Enter expression...')}
                        </div>
                        <div class="expression-error hidden text-xs text-red-600 dark:text-red-400 mt-1" data-condition-id="${condition.id}"></div>
                    </div>
                ` : `
                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">Default case - no expression needed</p>
                `}
            `;

            this.switchConditionsEditor.appendChild(conditionEl);

            // Add event listeners
            const removeBtn = conditionEl.querySelector('.remove-condition-modal-btn');
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    this.removeSwitchConditionFromModal(component, condition.id);
                });
            }

            const expressionInput = conditionEl.querySelector('.condition-expression-input');
            if (expressionInput) {
                expressionInput.addEventListener('input', (e) => {
                    condition.expression = e.target.value;
                    this.updateExpressionPreview(condition.id, e.target.value);
                    this.validateExpression(condition.id, e.target.value);
                });
            }

            const operatorHelper = conditionEl.querySelector('.operator-helper');
            if (operatorHelper) {
                operatorHelper.addEventListener('change', (e) => {
                    if (e.target.value) {
                        const input = conditionEl.querySelector('.condition-expression-input');
                        const cursorPos = input.selectionStart;
                        const textBefore = input.value.substring(0, cursorPos);
                        const textAfter = input.value.substring(cursorPos);
                        input.value = textBefore + e.target.value + textAfter;
                        condition.expression = input.value;
                        input.focus();
                        input.setSelectionRange(cursorPos + e.target.value.length, cursorPos + e.target.value.length);
                        e.target.value = '';
                        this.updateExpressionPreview(condition.id, input.value);
                        this.validateExpression(condition.id, input.value);
                    }
                });
            }
        });
    }

    // Add condition in modal
    addSwitchConditionInModal(type) {
        const component = this.currentEditingComponent;
        if (!component) return;

        const hasElse = component.conditions.some(c => c.type === 'else');
        if (hasElse) {
            alert('Cannot add more conditions after "Else"');
            return;
        }

        if (type === 'else' && hasElse) {
            alert('Else condition already exists');
            return;
        }

        const conditionId = `cond-${Date.now()}`;
        const newCondition = {
            type: type,
            expression: '',
            id: conditionId
        };

        component.conditions.push(newCondition);
        this.renderSwitchConditionsInModal(component);
    }

    // Remove condition from modal
    removeSwitchConditionFromModal(component, conditionId) {
        component.conditions = component.conditions.filter(c => c.id !== conditionId);
        this.renderSwitchConditionsInModal(component);
    }

    // Save switch configuration
    saveSwitchConfiguration() {
        const component = this.currentEditingComponent;
        if (!component) return;

        // Save switch mode
        const modeSelect = document.getElementById('switch-mode');
        component.switchMode = modeSelect.value;

        // Validate that all conditions have expressions (except else)
        const invalidConditions = component.conditions.filter(c =>
            c.type !== 'else' && (!c.expression || c.expression.trim() === '')
        );

        if (invalidConditions.length > 0) {
            alert('Please fill in all condition expressions');
            return;
        }

        // Update the component display
        this.renderSwitchConditions(component);

        // Close modal
        this.closeSwitchModal();
    }

    // Setup Expression Modal
    setupExpressionModal() {
        this.expressionModal = document.getElementById('expression-editor-modal');
        this.expressionRowsContainer = document.getElementById('expression-rows-container');

        // Close modal buttons
        document.getElementById('close-expression-modal').addEventListener('click', () => this.closeExpressionModal());
        document.getElementById('cancel-expression-modal').addEventListener('click', () => this.closeExpressionModal());

        // Click outside to close
        this.expressionModal.addEventListener('click', (e) => {
            if (e.target === this.expressionModal) {
                this.closeExpressionModal();
            }
        });

        // Add row button
        document.getElementById('add-expression-row').addEventListener('click', () => {
            this.addExpressionRow();
        });

        // Save button
        document.getElementById('save-expression').addEventListener('click', () => {
            this.saveExpressionConfiguration();
        });
    }

    // Open Expression Modal
    openExpressionModal(component) {
        this.currentEditingComponent = component;

        // Set modal title
        document.getElementById('expression-modal-title').textContent = component.id;

        // Initialize expression rows if not exists
        if (!component.expressionRows || component.expressionRows.length === 0) {
            component.expressionRows = [
                { key: 'status', expression: 'transaction.amount > 1_000 ? "green" : "red"' },
                { key: 'amount', expression: 'transaction.amount' }
            ];
        }

        // Render expression rows
        this.renderExpressionRows(component);

        // Show modal
        this.expressionModal.classList.remove('hidden');
    }

    // Close Expression Modal
    closeExpressionModal() {
        this.expressionModal.classList.add('hidden');
        this.currentEditingComponent = null;
    }

    // Render expression rows
    renderExpressionRows(component) {
        this.expressionRowsContainer.innerHTML = '';

        component.expressionRows.forEach((row, index) => {
            const rowEl = this.createExpressionRowElement(row, index);
            this.expressionRowsContainer.appendChild(rowEl);
        });
    }

    // Create expression row element
    createExpressionRowElement(row, index) {
        const rowEl = document.createElement('div');
        rowEl.className = 'grid grid-cols-12 gap-4 items-start bg-white p-4 rounded-lg border border-gray-200 group';
        rowEl.dataset.rowIndex = index;

        rowEl.innerHTML = `
            <div class="col-span-3">
                <input
                    type="text"
                    value="${this.escapeHtml(row.key)}"
                    placeholder="key"
                    class="expression-key-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    data-row-index="${index}"
                />
            </div>
            <div class="col-span-9 relative">
                <textarea
                    rows="2"
                    placeholder="Enter expression..."
                    class="expression-value-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    data-row-index="${index}"
                >${this.escapeHtml(row.expression)}</textarea>
                <button
                    class="remove-expression-row absolute -right-2 -top-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                    data-row-index="${index}"
                    title="Remove row"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        // Add event listeners
        const keyInput = rowEl.querySelector('.expression-key-input');
        const valueInput = rowEl.querySelector('.expression-value-input');
        const removeBtn = rowEl.querySelector('.remove-expression-row');

        keyInput.addEventListener('input', (e) => {
            this.updateExpressionRow(index, 'key', e.target.value);
        });

        valueInput.addEventListener('input', (e) => {
            this.updateExpressionRow(index, 'expression', e.target.value);
        });

        removeBtn.addEventListener('click', () => {
            this.removeExpressionRow(index);
        });

        return rowEl;
    }

    // Add expression row
    addExpressionRow() {
        const component = this.currentEditingComponent;
        if (!component) return;

        component.expressionRows.push({ key: '', expression: '' });
        this.renderExpressionRows(component);
    }

    // Update expression row
    updateExpressionRow(index, field, value) {
        const component = this.currentEditingComponent;
        if (!component || !component.expressionRows[index]) return;

        component.expressionRows[index][field] = value;
    }

    // Remove expression row
    removeExpressionRow(index) {
        const component = this.currentEditingComponent;
        if (!component) return;

        if (component.expressionRows.length <= 1) {
            alert('You must have at least one expression row');
            return;
        }

        component.expressionRows.splice(index, 1);
        this.renderExpressionRows(component);
    }

    // Save expression configuration
    saveExpressionConfiguration() {
        const component = this.currentEditingComponent;
        if (!component) return;

        // Validate that all rows have keys
        const invalidRows = component.expressionRows.filter(row => !row.key || row.key.trim() === '');

        if (invalidRows.length > 0) {
            alert('Please fill in all keys');
            return;
        }

        console.log('Saved expression configuration for', component.id, ':', component.expressionRows);

        // Close modal
        this.closeExpressionModal();
    }

    // Highlight expression syntax
    highlightExpression(expression) {
        if (!expression || expression === 'Enter expression...') {
            return '<span style="color: #94a3b8;">Enter expression...</span>';
        }

        // Escape HTML first
        let highlighted = this.escapeHtml(expression);

        // Highlight strings (single and double quotes) - green
        highlighted = highlighted.replace(/('([^']*)'|"([^"]*)")/g, '<span style="color: #10b981;">$1</span>');

        // Highlight operators - purple/bold
        highlighted = highlighted.replace(/(\s+)(==|!=|>=|<=|>|<|IN|NOT IN|&&|\|\|)(\s+)/g, '$1<span style="color: #a855f7; font-weight: bold;">$2</span>$3');

        // Highlight numbers - blue
        highlighted = highlighted.replace(/\b(\d+\.?\d*)\b/g, '<span style="color: #3b82f6;">$1</span>');

        // Highlight property access (dot notation) - orange
        highlighted = highlighted.replace(/\b([a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_.]*)/g, '<span style="color: #f97316;">$1</span>');

        return highlighted;
    }

    // Update expression preview
    updateExpressionPreview(conditionId, expression) {
        const preview = document.querySelector(`.expression-preview[data-condition-id="${conditionId}"]`);
        if (preview) {
            preview.innerHTML = this.highlightExpression(expression);
        }
    }

    // Validate expression
    validateExpression(conditionId, expression) {
        const errorEl = document.querySelector(`.expression-error[data-condition-id="${conditionId}"]`);
        if (!errorEl) return;

        if (!expression || expression.trim() === '') {
            errorEl.classList.add('hidden');
            return;
        }

        // Basic validation rules
        const errors = [];

        // Check for unmatched quotes
        const singleQuotes = (expression.match(/'/g) || []).length;
        const doubleQuotes = (expression.match(/"/g) || []).length;
        if (singleQuotes % 2 !== 0) errors.push('Unmatched single quote');
        if (doubleQuotes % 2 !== 0) errors.push('Unmatched double quote');

        // Check for unmatched parentheses
        const openParens = (expression.match(/\(/g) || []).length;
        const closeParens = (expression.match(/\)/g) || []).length;
        if (openParens !== closeParens) errors.push('Unmatched parentheses');

        // Check for invalid operators
        if (/={3,}|!={2,}/.test(expression)) errors.push('Invalid operator (use == or !=)');

        if (errors.length > 0) {
            errorEl.textContent = '⚠️ ' + errors.join(', ');
            errorEl.classList.remove('hidden');
        } else {
            errorEl.classList.add('hidden');
        }
    }

    // Escape HTML
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize the graph editor when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('canvas')) {
        window.graphEditor = new GraphEditor();
    }
});

