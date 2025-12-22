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
                <button class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Configure</button>
            </div>
        `;

        this.canvasContent.appendChild(componentEl);
        this.addConnectionDots(componentEl);
        this.setupComponentDragging(componentEl);
        this.setupComponentDeletion(componentEl);

        this.components.push({
            id: componentId,
            type: type,
            element: componentEl,
            x: x,
            y: y
        });
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
}

// Initialize the graph editor when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('canvas')) {
        window.graphEditor = new GraphEditor();
    }
});

