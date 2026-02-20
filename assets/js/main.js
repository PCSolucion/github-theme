/**
 * JavaScript principal para GitHub Theme (Vanilla JS Version)
 * Independiente de jQuery para mejorar el rendimiento.
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // 1. Smooth scroll para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const offset = 120;
                const bodyRect = document.body.getBoundingClientRect().top;
                const elementRect = target.getBoundingClientRect().top;
                const elementPosition = elementRect - bodyRect;
                const offsetPosition = elementPosition - offset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // 2. Mejorar la experiencia del formulario de búsqueda
    document.querySelectorAll('.search-form input[type="search"]').forEach(input => {
        const form = input.closest('.search-form');
        if (!form) return;

        input.addEventListener('focus', () => form.classList.add('focused'));
        input.addEventListener('blur', () => form.classList.remove('focused'));
    });

    // 3. Animación suave para elementos al hacer scroll (IntersectionObserver)
    if (window.IntersectionObserver) {
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

        document.querySelectorAll('.post-item, .single-post, .page-content').forEach(el => {
            el.classList.add('observe');
            observer.observe(el);
        });
    }

    // 4. Mejorar la navegación móvil
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNavigation = document.querySelector('.main-navigation');

    if (mobileMenuToggle && mainNavigation) {
        mobileMenuToggle.addEventListener('click', (e) => {
            e.preventDefault();
            mainNavigation.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
        });

        // Cerrar menú móvil al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!mainNavigation.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                mainNavigation.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
            }
        });
    }

    // 5. Funcionalidad de Copiar al Portapapeles (Solo en posts)
    if (document.querySelector('.entry-content')) {
        document.querySelectorAll('pre').forEach(pre => {
            pre.style.position = 'relative';
            const button = document.createElement('button');
            button.className = 'copy-button';
            button.setAttribute('aria-label', 'Copiar al portapapeles');
            button.innerHTML = '<svg aria-hidden="true" viewBox="0 0 16 16" version="1.1"><path fill-rule="evenodd" d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 010 1.5h-1.5a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-1.5a.75.75 0 011.5 0v1.5A1.75 1.75 0 019.25 16h-7.5A1.75 1.75 0 010 14.25v-7.5z"></path><path fill-rule="evenodd" d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0114.25 11h-7.5A1.75 1.75 0 015 9.25v-7.5zm1.75-.25a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-7.5a.25.25 0 00-.25-.25h-7.5z"></path></svg>';
            
            pre.appendChild(button);

            button.addEventListener('click', () => {
                const code = pre.querySelector('code');
                if (!code) return;

                navigator.clipboard.writeText(code.textContent).then(() => {
                    button.classList.add('copied');
                    setTimeout(() => button.classList.remove('copied'), 2000);
                }).catch(err => {
                    console.error('Error al copiar: ', err);
                });
            });
        });
    }

    // 6. ScrollSpy para Tabla de Contenidos (TOC)
    function initTOC() {
        const tocContainer = document.getElementById('table-of-contents');
        const contentArea = document.querySelector('.entry-content');
        if (!tocContainer || !contentArea) return;

        const headings = contentArea.querySelectorAll('h2, h3');
        const validHeadings = Array.from(headings).filter(h => h.textContent.trim().length > 0);

        if (validHeadings.length === 0) {
            const tocBox = document.querySelector('.toc-box');
            if (tocBox) tocBox.style.display = 'none';
            return;
        }

        // Scroll Spy mejorado
        if (window.IntersectionObserver) {
            const spyOptions = {
                rootMargin: '-50px 0px -80% 0px', // Detectar al entrar en el tercio superior
                threshold: [0, 1]
            };

            const spyObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        const link = tocContainer.querySelector(`a[href="#${id}"]`);
                        if (link) {
                            tocContainer.querySelectorAll('a').forEach(l => l.classList.remove('active'));
                            link.classList.add('active');
                        }
                    }
                });
            }, spyOptions);

            validHeadings.forEach(h => spyObserver.observe(h));
        }
    }
    initTOC();


    // 8. Tooltip dinámico para Calendario de Contribuciones (Solo en Home)
    function initContributionsTooltip() {
        if (!document.querySelector('.contribution-cell')) return;

        const tooltip = document.createElement('div');
        tooltip.id = 'github-tooltip';
        tooltip.className = 'github-tooltip';
        document.body.appendChild(tooltip);

        document.addEventListener('mouseover', (e) => {
            const cell = e.target.closest('.contribution-cell[data-tooltip]');
            if (!cell) return;

            const text = cell.dataset.tooltip;
            const titles = cell.dataset.titles;
            const dateStr = cell.dataset.date;
            
            if (!text || !dateStr) return;

            // No mostrar si la fecha es futura (comparación basada en fecha local)
            const dateParts = dateStr.split('-');
            const cellDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (cellDate > today) return;

            let content = `<div style="font-weight:600; margin-bottom:4px;">${text}</div>`;
            if (titles) {
                const titleList = titles.split('|||');
                content += '<div style="font-size:11px; color:#8b949e; border-top:1px solid rgba(255,255,255,0.1); padding-top:4px; margin-top:4px;">';
                titleList.forEach(title => content += `<div style="margin-bottom:2px;">• ${title}</div>`);
                content += '</div>';
            }

            tooltip.innerHTML = content;
            tooltip.style.opacity = '1';
        });

        document.addEventListener('mouseout', (e) => {
            if (e.target.closest('.contribution-cell[data-tooltip]')) {
                tooltip.style.opacity = '0';
            }
        });

        document.addEventListener('mousemove', (e) => {
            if (tooltip.style.opacity === '0') return;

            // Volver a posicionar arriba por defecto
            const tooltipHeight = tooltip.offsetHeight;
            const tooltipWidth = tooltip.offsetWidth;
            const offset = 15;

            let top = e.clientY - tooltipHeight - offset;
            let left = e.clientX;

            // Si no hay espacio arriba, mostrar abajo
            if (top < 10) {
                tooltip.classList.add('bottom');
                top = e.clientY + offset;
            } else {
                tooltip.classList.remove('bottom');
            }

            if (left - (tooltipWidth / 2) < 10) left = (tooltipWidth / 2) + 10;
            else if (left + (tooltipWidth / 2) > window.innerWidth - 10) left = window.innerWidth - 10 - (tooltipWidth / 2);

            tooltip.style.top = top + 'px';
            tooltip.style.left = left + 'px';
            
            // Añadir transición suave (opcional, necesita CSS pero lo activamos aquí)
            tooltip.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            tooltip.style.transform = 'translate(-50%, 0)';
        });
    }
    initContributionsTooltip();

    // 9. Fixes visuales (Vanilla)
    function fixVisuals() {
        // Enlaces con imágenes
        document.querySelectorAll('.entry-content a').forEach(a => {
            if (a.querySelector('img')) {
                a.classList.add('image-link');
                a.style.border = 'none';
                a.style.textDecoration = 'none';
                a.style.background = 'none';
                a.style.padding = '0';
            }
        });

        // Encabezados vacíos
        document.querySelectorAll('.entry-content h2, .entry-content h3, .entry-content h4').forEach(h => {
            if (h.textContent.trim().length === 0) {
                h.style.display = 'none';
                h.classList.add('hidden-heading');
            }
        });
    }
    fixVisuals();

    // 10. Mejora Bloques de Código (Vanilla)
    function enhanceCodeBlocks() {
        document.querySelectorAll('pre code').forEach(code => {
            const pre = code.parentElement;
            
            // Etiqueta lenguaje ahora se maneja en el servidor (SSR) via inc/optimization.php
            /*
            const classes = code.className || '';
            const langMatch = classes.match(/language-([a-z0-9]+)|lang-([a-z0-9]+)/);
            const lang = langMatch ? (langMatch[1] || langMatch[2]) : '';

            if (lang && !pre.querySelector('.code-language-label')) {
                const label = document.createElement('div');
                label.className = 'code-language-label';
                label.textContent = lang;
                pre.appendChild(label);
            }
            */

            // Números de línea
            const text = code.textContent;
            const lines = text.split('\n');
            if (lines.length > 0 && lines[lines.length - 1] === '') lines.pop();
            
            if (lines.length > 1) {
                const lineNumbers = document.createElement('span');
                lineNumbers.setAttribute('aria-hidden', 'true');
                lineNumbers.className = 'line-numbers-rows';
                for (let i = 0; i < lines.length; i++) {
                    lineNumbers.appendChild(document.createElement('span'));
                }
                pre.prepend(lineNumbers);
                pre.classList.add('line-numbers');
            }
        });
    }
    enhanceCodeBlocks();

});
