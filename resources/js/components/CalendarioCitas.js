export default class CalendarioCitas {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.options = {
            terapeutaId: options.terapeutaId || null,
            duracion: options.duracion || 60,
            onDateSelect: options.onDateSelect || (() => {}),
            onTimeSelect: options.onTimeSelect || (() => {}),
            ...options
        };
        
        this.fechaActual = new Date();
        this.fechaSeleccionada = null;
        this.horaSeleccionada = null;
        this.datosCalendario = null;
        
        this.init();
    }

    init() {
        this.render();
        this.cargarDatos();
    }

    render() {
        this.container.innerHTML = `
            <div class="calendario-citas">
                <div class="calendario-header">
                    <button type="button" class="btn-nav" id="prevMonth">‹</button>
                    <h3 id="mesAnio"></h3>
                    <button type="button" class="btn-nav" id="nextMonth">›</button>
                </div>
                
                <div class="calendario-loading" id="calendarioLoading">
                    <div class="spinner"></div>
                    <p>Cargando disponibilidad...</p>
                </div>
                
                <div class="calendario-grid" id="calendarioGrid" style="display: none;">
                    <div class="dias-semana">
                        <div class="dia-semana">Lun</div>
                        <div class="dia-semana">Mar</div>
                        <div class="dia-semana">Mié</div>
                        <div class="dia-semana">Jue</div>
                        <div class="dia-semana">Vie</div>
                        <div class="dia-semana">Sáb</div>
                        <div class="dia-semana">Dom</div>
                    </div>
                    <div class="dias-mes" id="diasMes"></div>
                </div>
                
                <div class="selector-horas" id="selectorHoras" style="display: none;">
                    <h4>Selecciona una hora:</h4>
                    <div class="horas-grid" id="horasGrid"></div>
                </div>
                
                <div class="calendario-leyenda">
                    <div class="leyenda-item">
                        <div class="color-box disponible"></div>
                        <span>Disponible</span>
                    </div>
                    <div class="leyenda-item">
                        <div class="color-box ocupado"></div>
                        <span>Ocupado</span>
                    </div>
                    <div class="leyenda-item">
                        <div class="color-box no-laboral"></div>
                        <span>No laboral</span>
                    </div>
                </div>
            </div>
        `;
        
        this.bindEvents();
    }

    bindEvents() {
        // Navegación de meses
        document.getElementById('prevMonth').addEventListener('click', () => {
            this.cambiarMes(-1);
        });
        
        document.getElementById('nextMonth').addEventListener('click', () => {
            this.cambiarMes(1);
        });
    }

    cambiarMes(direccion) {
        this.fechaActual.setMonth(this.fechaActual.getMonth() + direccion);
        this.cargarDatos();
    }

    async cargarDatos() {
        if (!this.options.terapeutaId) {
            console.error('No se ha especificado un terapeuta');
            return;
        }

        const loading = document.getElementById('calendarioLoading');
        const grid = document.getElementById('calendarioGrid');
        
        loading.style.display = 'block';
        grid.style.display = 'none';

        try {
            const response = await fetch('/api/citas/calendario-disponibilidad', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    terapeuta_id: this.options.terapeutaId,
                    mes: this.fechaActual.getMonth() + 1,
                    anio: this.fechaActual.getFullYear(),
                    duracion: this.options.duracion
                })
            });

            if (!response.ok) {
                throw new Error('Error al cargar los datos del calendario');
            }

            this.datosCalendario = await response.json();
            this.renderCalendario();
            
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error al cargar la disponibilidad del calendario');
        } finally {
            loading.style.display = 'none';
            grid.style.display = 'block';
        }
    }

    renderCalendario() {
        const mesAnio = document.getElementById('mesAnio');
        const diasMes = document.getElementById('diasMes');
        
        // Actualizar título del mes
        const nombreMes = new Intl.DateTimeFormat('es-ES', { 
            month: 'long', 
            year: 'numeric' 
        }).format(this.fechaActual);
        
        mesAnio.textContent = nombreMes.charAt(0).toUpperCase() + nombreMes.slice(1);
        
        // Limpiar días previos
        diasMes.innerHTML = '';
        
        // Obtener primer día del mes y cuántos días tiene
        const primerDia = new Date(this.fechaActual.getFullYear(), this.fechaActual.getMonth(), 1);
        const ultimoDia = new Date(this.fechaActual.getFullYear(), this.fechaActual.getMonth() + 1, 0);
        
        // Agregar días vacíos al inicio (para que inicie en lunes)
        const diaInicio = primerDia.getDay() === 0 ? 6 : primerDia.getDay() - 1;
        for (let i = 0; i < diaInicio; i++) {
            const divVacio = document.createElement('div');
            divVacio.className = 'dia-vacio';
            diasMes.appendChild(divVacio);
        }
        
        // Agregar días del mes
        for (let dia = 1; dia <= ultimoDia.getDate(); dia++) {
            const fechaStr = `${this.fechaActual.getFullYear()}-${String(this.fechaActual.getMonth() + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
            const datoDia = this.datosCalendario.calendario[fechaStr];
            
            const divDia = document.createElement('div');
            divDia.className = 'dia';
            divDia.textContent = dia;
            divDia.setAttribute('data-fecha', fechaStr);
            
            if (datoDia) {
                if (datoDia.disponible) {
                    divDia.classList.add('disponible');
                    divDia.title = `${datoDia.total_disponibles} horas disponibles`;
                } else if (datoDia.motivo === 'domingo') {
                    divDia.classList.add('domingo');
                    divDia.title = 'Domingo - No laboral';
                } else if (datoDia.motivo === 'fecha_pasada') {
                    divDia.classList.add('pasado');
                    divDia.title = 'Fecha pasada';
                } else {
                    divDia.classList.add('ocupado');
                    divDia.title = 'Sin horarios disponibles';
                }
                
                // Agregar evento click solo para días disponibles
                if (datoDia.disponible) {
                    divDia.addEventListener('click', () => {
                        this.seleccionarFecha(fechaStr, datoDia);
                    });
                }
            }
            
            diasMes.appendChild(divDia);
        }
    }

    seleccionarFecha(fecha, datoDia) {
        // Remover selección anterior
        document.querySelectorAll('.dia.seleccionado').forEach(dia => {
            dia.classList.remove('seleccionado');
        });
        
        // Agregar selección a la fecha actual
        document.querySelector(`[data-fecha="${fecha}"]`).classList.add('seleccionado');
        
        this.fechaSeleccionada = fecha;
        this.horaSeleccionada = null;
        
        // Mostrar selector de horas
        this.mostrarSelectorHoras(datoDia);
        
        // Callback
        this.options.onDateSelect(fecha, datoDia);
    }

    mostrarSelectorHoras(datoDia) {
        const selectorHoras = document.getElementById('selectorHoras');
        const horasGrid = document.getElementById('horasGrid');
        
        horasGrid.innerHTML = '';
        
        // Crear botones para horas disponibles
        datoDia.horas_disponibles.forEach(hora => {
            const btnHora = document.createElement('button');
            btnHora.type = 'button';
            btnHora.className = 'btn-hora disponible';
            btnHora.textContent = hora;
            btnHora.addEventListener('click', () => {
                this.seleccionarHora(hora);
            });
            horasGrid.appendChild(btnHora);
        });
        
        // Crear botones para horas ocupadas (deshabilitados)
        datoDia.horas_ocupadas.forEach(hora => {
            const btnHora = document.createElement('button');
            btnHora.type = 'button';
            btnHora.className = 'btn-hora ocupado';
            btnHora.textContent = hora;
            btnHora.disabled = true;
            btnHora.title = 'Hora ocupada';
            horasGrid.appendChild(btnHora);
        });
        
        // Ordenar botones por hora
        const botones = Array.from(horasGrid.children);
        botones.sort((a, b) => a.textContent.localeCompare(b.textContent));
        horasGrid.innerHTML = '';
        botones.forEach(btn => horasGrid.appendChild(btn));
        
        selectorHoras.style.display = 'block';
    }

    seleccionarHora(hora) {
        // Remover selección anterior
        document.querySelectorAll('.btn-hora.seleccionado').forEach(btn => {
            btn.classList.remove('seleccionado');
        });
        
        // Agregar selección a la hora actual
        document.querySelector(`.btn-hora[data-hora="${hora}"]`)?.classList.add('seleccionado') ||
        event.target.classList.add('seleccionado');
        
        this.horaSeleccionada = hora;
        
        // Callback
        this.options.onTimeSelect(hora, this.fechaSeleccionada);
    }

    mostrarError(mensaje) {
        const loading = document.getElementById('calendarioLoading');
        loading.innerHTML = `
            <div class="error-message">
                <p>❌ ${mensaje}</p>
                <button onclick="location.reload()">Reintentar</button>
            </div>
        `;
    }

    // Métodos públicos
    getFechaSeleccionada() {
        return this.fechaSeleccionada;
    }

    getHoraSeleccionada() {
        return this.horaSeleccionada;
    }

    getFechaHoraSeleccionada() {
        if (this.fechaSeleccionada && this.horaSeleccionada) {
            return `${this.fechaSeleccionada} ${this.horaSeleccionada}:00`;
        }
        return null;
    }

    setTerapeuta(terapeutaId) {
        this.options.terapeutaId = terapeutaId;
        this.cargarDatos();
    }

    reset() {
        this.fechaSeleccionada = null;
        this.horaSeleccionada = null;
        document.querySelectorAll('.seleccionado').forEach(el => {
            el.classList.remove('seleccionado');
        });
        document.getElementById('selectorHoras').style.display = 'none';
    }
}