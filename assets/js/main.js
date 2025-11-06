/* ================================
   BARBER LEAGUE - JAVASCRIPT
   Sistema completo con validaciones
   ================================ */

// === CONFIGURACIÓN GLOBAL ===
const CONFIG = {
  horarioAtencion: {
    inicio: '09:00',
    fin: '21:00'
  },
  duracionServicio: 60, // minutos
  duracionCancha: 60, // minutos
  intervaloReservas: 30 // minutos entre franjas
};

// === INICIALIZACIÓN ===
document.addEventListener('DOMContentLoaded', function() {
  initHeader();
  initMobileMenu();
  initScrollAnimations();
  initFormValidation();
  initDateTimePickers();
  initReservationSystem();
  initPreloader();
  initSmoothScroll();
});

// === HEADER SCROLLING ===
function initHeader() {
  const header = document.querySelector('.header');
  if (!header) return;
  
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });
}

// === MENÚ MÓVIL ===
function initMobileMenu() {
  const menuToggle = document.querySelector('.menu-toggle');
  const navMenu = document.querySelector('.nav-menu');
  const navLinks = document.querySelectorAll('.nav-link');
  
  if (!menuToggle || !navMenu) return;
  
  // Toggle del menú
  menuToggle.addEventListener('click', () => {
    menuToggle.classList.toggle('active');
    navMenu.classList.toggle('active');
    document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
  });
  
  // Cerrar menú al hacer click en un link
  navLinks.forEach(link => {
    link.addEventListener('click', () => {
      menuToggle.classList.remove('active');
      navMenu.classList.remove('active');
      document.body.style.overflow = '';
    });
  });
  
  // Cerrar menú al hacer click fuera
  document.addEventListener('click', (e) => {
    if (!menuToggle.contains(e.target) && !navMenu.contains(e.target)) {
      menuToggle.classList.remove('active');
      navMenu.classList.remove('active');
      document.body.style.overflow = '';
    }
  });
}

// === ANIMACIONES AL SCROLL ===
function initScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('fade-in');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);
  
  // Observar elementos
  const animatedElements = document.querySelectorAll('.service-card, .cancha-card, .admin-card');
  animatedElements.forEach(el => observer.observe(el));
}

// === SMOOTH SCROLL ===
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (href === '#') return;
      
      e.preventDefault();
      const target = document.querySelector(href);
      
      if (target) {
        const headerOffset = 80;
        const elementPosition = target.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
        
        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        });
      }
    });
  });
}

// === VALIDACIÓN DE FORMULARIOS ===
function initFormValidation() {
  const forms = document.querySelectorAll('form');
  
  forms.forEach(form => {
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      if (validateForm(this)) {
        const formData = new FormData(this);
        
        // Mostrar spinner
        showSpinner(this);
        
        // Simular envío (aquí iría la llamada AJAX real)
        setTimeout(() => {
          hideSpinner(this);
          showAlert('Reserva confirmada exitosamente!', 'success');
          this.reset();
        }, 1500);
      }
    });
    
    // Validación en tiempo real
    const inputs = form.querySelectorAll('.form-control');
    inputs.forEach(input => {
      input.addEventListener('blur', function() {
        validateField(this);
      });
      
      input.addEventListener('input', function() {
        if (this.classList.contains('error')) {
          validateField(this);
        }
      });
    });
  });
}

function validateForm(form) {
  let isValid = true;
  const inputs = form.querySelectorAll('.form-control');
  
  inputs.forEach(input => {
    if (!validateField(input)) {
      isValid = false;
    }
  });
  
  return isValid;
}

function validateField(input) {
  const value = input.value.trim();
  const type = input.type;
  const name = input.name;
  let errorMessage = '';
  let isValid = true;
  
  // Limpiar errores previos
  clearFieldError(input);
  
  // Validar campos requeridos
  if (input.hasAttribute('required') && !value) {
    errorMessage = 'Este campo es obligatorio';
    isValid = false;
  }
  
  // Validaciones específicas
  if (value && isValid) {
    switch(name) {
      case 'nombre':
        if (value.length < 3) {
          errorMessage = 'El nombre debe tener al menos 3 caracteres';
          isValid = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(value)) {
          errorMessage = 'El nombre solo puede contener letras';
          isValid = false;
        }
        break;
        
      case 'telefono':
        // Validación para números colombianos (10 dígitos)
        if (!/^[0-9]{10}$/.test(value.replace(/\s/g, ''))) {
          errorMessage = 'Ingrese un teléfono válido (10 dígitos)';
          isValid = false;
        }
        break;
        
      case 'correo':
      case 'email':
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
          errorMessage = 'Ingrese un correo electrónico válido';
          isValid = false;
        }
        break;
        
      case 'fecha':
        if (!isValidDate(value)) {
          errorMessage = 'Seleccione una fecha válida';
          isValid = false;
        }
        break;
        
      case 'hora':
        if (!isValidTime(value)) {
          errorMessage = 'Seleccione una hora dentro del horario de atención';
          isValid = false;
        }
        break;
    }
  }
  
  if (!isValid) {
    showFieldError(input, errorMessage);
  }
  
  return isValid;
}

function showFieldError(input, message) {
  input.classList.add('error');
  
  let errorDiv = input.parentElement.querySelector('.form-error');
  if (!errorDiv) {
    errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    input.parentElement.appendChild(errorDiv);
  }
  
  errorDiv.textContent = message;
  errorDiv.style.display = 'block';
}

function clearFieldError(input) {
  input.classList.remove('error');
  const errorDiv = input.parentElement.querySelector('.form-error');
  if (errorDiv) {
    errorDiv.style.display = 'none';
  }
}

// === VALIDACIONES ESPECÍFICAS ===
function isValidDate(dateString) {
  const fecha = new Date(dateString);
  const hoy = new Date();
  hoy.setHours(0, 0, 0, 0);
  
  // La fecha debe ser hoy o en el futuro
  return fecha >= hoy;
}

function isValidTime(timeString) {
  const [hora, minuto] = timeString.split(':').map(Number);
  const [horaInicio, minutoInicio] = CONFIG.horarioAtencion.inicio.split(':').map(Number);
  const [horaFin, minutoFin] = CONFIG.horarioAtencion.fin.split(':').map(Number);
  
  const tiempoMinutos = hora * 60 + minuto;
  const inicioMinutos = horaInicio * 60 + minutoInicio;
  const finMinutos = horaFin * 60 + minutoFin;
  
  return tiempoMinutos >= inicioMinutos && tiempoMinutos <= finMinutos;
}

// === DATE & TIME PICKERS ===
function initDateTimePickers() {
  const fechaInput = document.querySelector('input[name="fecha"]');
  const horaInput = document.querySelector('input[name="hora"]');
  
  if (fechaInput) {
    // Establecer fecha mínima (hoy)
    const hoy = new Date().toISOString().split('T')[0];
    fechaInput.setAttribute('min', hoy);
    
    // Establecer fecha máxima (3 meses adelante)
    const maxFecha = new Date();
    maxFecha.setMonth(maxFecha.getMonth() + 3);
    fechaInput.setAttribute('max', maxFecha.toISOString().split('T')[0]);
  }
  
  if (horaInput) {
    // Configurar restricciones de hora
    horaInput.setAttribute('min', CONFIG.horarioAtencion.inicio);
    horaInput.setAttribute('max', CONFIG.horarioAtencion.fin);
    horaInput.setAttribute('step', CONFIG.intervaloReservas * 60); // en segundos
  }
}

// === SISTEMA DE RESERVAS ===
function initReservationSystem() {
  const servicioSelect = document.querySelector('select[name="servicio"]');
  const fechaInput = document.querySelector('input[name="fecha"]');
  const horaInput = document.querySelector('input[name="hora"]');
  
  if (!servicioSelect || !fechaInput || !horaInput) return;
  
  // Al cambiar fecha o servicio, verificar disponibilidad
  fechaInput.addEventListener('change', () => checkAvailability());
  servicioSelect.addEventListener('change', () => checkAvailability());
}

async function checkAvailability() {
  const fechaInput = document.querySelector('input[name="fecha"]');
  const servicioSelect = document.querySelector('select[name="servicio"]');
  
  if (!fechaInput.value || !servicioSelect.value) return;
  
  // Aquí iría la llamada AJAX para verificar disponibilidad
  // Por ahora, solo mostramos las horas disponibles
  generateTimeSlots(fechaInput.value);
}

function generateTimeSlots(fecha) {
  const horaInput = document.querySelector('input[name="hora"]');
  if (!horaInput) return;
  
  // Esta función generaría las franjas horarias disponibles
  // basándose en reservas existentes
  console.log('Generando franjas horarias para:', fecha);
}

// === ALERTAS Y NOTIFICACIONES ===
function showAlert(message, type = 'success') {
  // Crear elemento de alerta
  const alert = document.createElement('div');
  alert.className = `alert alert-${type} fade-in`;
  alert.textContent = message;
  
  // Insertar al inicio del body o en un contenedor específico
  const container = document.querySelector('.container') || document.body;
  container.insertBefore(alert, container.firstChild);
  
  // Auto-remover después de 5 segundos
  setTimeout(() => {
    alert.style.opacity = '0';
    setTimeout(() => alert.remove(), 300);
  }, 5000);
}

// === SPINNER DE CARGA ===
function showSpinner(form) {
  const submitBtn = form.querySelector('button[type="submit"]');
  if (!submitBtn) return;
  
  submitBtn.disabled = true;
  submitBtn.dataset.originalText = submitBtn.textContent;
  submitBtn.innerHTML = '<div class="spinner"></div>';
}

function hideSpinner(form) {
  const submitBtn = form.querySelector('button[type="submit"]');
  if (!submitBtn) return;
  
  submitBtn.disabled = false;
  submitBtn.textContent = submitBtn.dataset.originalText || 'Enviar';
}

// === PRELOADER ===
function initPreloader() {
  const preloader = document.querySelector('.preloader');
  if (!preloader) return;
  
  window.addEventListener('load', () => {
    setTimeout(() => {
      preloader.classList.add('hidden');
      setTimeout(() => preloader.remove(), 500);
    }, 800);
  });
}

// === FUNCIONES AUXILIARES ===

// Formatear teléfono colombiano
function formatPhoneNumber(input) {
  let value = input.value.replace(/\D/g, '');
  if (value.length > 10) value = value.slice(0, 10);
  
  if (value.length >= 6) {
    value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6);
  } else if (value.length >= 3) {
    value = value.slice(0, 3) + ' ' + value.slice(3);
  }
  
  input.value = value;
}

// Aplicar formato automático a inputs de teléfono
document.addEventListener('DOMContentLoaded', () => {
  const phoneInputs = document.querySelectorAll('input[name="telefono"]');
  phoneInputs.forEach(input => {
    input.addEventListener('input', () => formatPhoneNumber(input));
  });
});

// === FUNCIONES PARA AJAX ===

// Enviar reserva por AJAX
async function enviarReserva(formData) {
  try {
    const response = await fetch('reservar.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      showAlert(data.message || 'Reserva realizada con éxito', 'success');
      return true;
    } else {
      showAlert(data.message || 'Error al procesar la reserva', 'error');
      return false;
    }
  } catch (error) {
    console.error('Error:', error);
    showAlert('Error de conexión. Intente nuevamente.', 'error');
    return false;
  }
}

// Verificar disponibilidad por AJAX
async function verificarDisponibilidad(fecha, hora, servicio) {
  try {
    const response = await fetch('api/check-availability.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ fecha, hora, servicio })
    });
    
    const data = await response.json();
    return data.disponible;
  } catch (error) {
    console.error('Error:', error);
    return false;
  }
}

// === FUNCIONES ADMIN PANEL ===

// Marcar reserva como atendida
function marcarAtendida(reservaId) {
  if (!confirm('¿Marcar esta reserva como atendida?')) return;
  
  fetch(`admin/procesar-reserva.php?id=${reservaId}&accion=atender`, {
    method: 'POST'
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlert('Reserva marcada como atendida', 'success');
      location.reload();
    } else {
      showAlert('Error al procesar', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showAlert('Error de conexión', 'error');
  });
}

// Eliminar reserva
function eliminarReserva(reservaId) {
  if (!confirm('¿Está seguro de eliminar esta reserva?')) return;
  
  fetch(`admin/procesar-reserva.php?id=${reservaId}&accion=eliminar`, {
    method: 'POST'
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlert('Reserva eliminada', 'success');
      location.reload();
    } else {
      showAlert('Error al eliminar', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showAlert('Error de conexión', 'error');
  });
}

// === FILTROS Y BÚSQUEDA ===

// Filtrar servicios
function filtrarServicios(criterio) {
  const servicios = document.querySelectorAll('.service-card');
  
  servicios.forEach(servicio => {
    const nombre = servicio.querySelector('.service-title').textContent.toLowerCase();
    const descripcion = servicio.querySelector('.service-description').textContent.toLowerCase();
    
    if (nombre.includes(criterio.toLowerCase()) || descripcion.includes(criterio.toLowerCase())) {
      servicio.style.display = 'block';
    } else {
      servicio.style.display = 'none';
    }
  });
}

// Ordenar servicios por precio
function ordenarPorPrecio(orden = 'asc') {
  const grid = document.querySelector('.services-grid');
  if (!grid) return;
  
  const servicios = Array.from(grid.querySelectorAll('.service-card'));
  
  servicios.sort((a, b) => {
    const precioA = parseFloat(a.querySelector('.service-price').textContent.replace(/[^0-9.]/g, ''));
    const precioB = parseFloat(b.querySelector('.service-price').textContent.replace(/[^0-9.]/g, ''));
    
    return orden === 'asc' ? precioA - precioB : precioB - precioA;
  });
  
  servicios.forEach(servicio => grid.appendChild(servicio));
}

// === UTILIDADES ===

// Debounce para optimizar eventos
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Throttle para scroll events
function throttle(func, limit) {
  let inThrottle;
  return function() {
    const args = arguments;
    const context = this;
    if (!inThrottle) {
      func.apply(context, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
}

// === EXPORTAR FUNCIONES GLOBALES ===
window.BarberLeague = {
  showAlert,
  verificarDisponibilidad,
  marcarAtendida,
  eliminarReserva,
  filtrarServicios,
  ordenarPorPrecio
};

console.log('Barber League JS Cargado ✓');