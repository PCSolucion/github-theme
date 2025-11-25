/**
 * JavaScript principal para GitHub Theme
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        // Smooth scroll para enlaces internos
        $('a[href^="#"]').on('click', function (e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 80
                }, 1000);
            }
        });

        // Mejorar la experiencia del formulario de búsqueda
        $('.search-form input[type="search"]').on('focus', function () {
            $(this).closest('.search-form').addClass('focused');
        }).on('blur', function () {
            $(this).closest('.search-form').removeClass('focused');
        });

        // Animación suave para elementos al hacer scroll
        if (window.IntersectionObserver) {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observar elementos con la clase 'observe'
            document.querySelectorAll('.post-item, .single-post, .page-content').forEach(function (el) {
                el.classList.add('observe');
                observer.observe(el);
            });
        }

        // Lazy loading para imágenes (si el navegador no lo soporta nativamente)
        if ('loading' in HTMLImageElement.prototype === false) {
            const images = document.querySelectorAll('img[loading="lazy"]');

            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver(function (entries, observer) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });

                images.forEach(function (img) {
                    imageObserver.observe(img);
                });
            }
        }

        // Mejorar la navegación móvil
        var $mobileMenuToggle = $('.mobile-menu-toggle');
        var $mainNavigation = $('.main-navigation');

        if ($mobileMenuToggle.length) {
            $mobileMenuToggle.on('click', function (e) {
                e.preventDefault();
                $mainNavigation.toggleClass('active');
                $(this).toggleClass('active');
            });
        }

        // Cerrar menú móvil al hacer clic fuera
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.main-navigation, .mobile-menu-toggle').length) {
                $mainNavigation.removeClass('active');
                $mobileMenuToggle.removeClass('active');
            }
        });

    });

    // Funcionalidad de Copiar al Portapapeles
    $(document).ready(function () {
        $('pre').each(function () {
            var $pre = $(this);
            var $button = $('<button class="copy-button" aria-label="Copiar al portapapeles"><svg aria-hidden="true" viewBox="0 0 16 16" version="1.1"><path fill-rule="evenodd" d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 010 1.5h-1.5a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-1.5a.75.75 0 011.5 0v1.5A1.75 1.75 0 019.25 16h-7.5A1.75 1.75 0 010 14.25v-7.5z"></path><path fill-rule="evenodd" d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0114.25 11h-7.5A1.75 1.75 0 015 9.25v-7.5zm1.75-.25a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-7.5a.25.25 0 00-.25-.25h-7.5z"></path></svg></button>');

            $pre.css('position', 'relative');
            $pre.append($button);

            $button.on('click', function () {
                var code = $pre.find('code').text();

                navigator.clipboard.writeText(code).then(function () {
                    $button.addClass('copied');
                    setTimeout(function () {
                        $button.removeClass('copied');
                    }, 2000);
                }, function (err) {
                    console.error('Error al copiar: ', err);
                });
            });
        });
    });

    // Generar Tabla de Contenidos (TOC)
    function generateTOC() {
        var $tocContainer = $('#table-of-contents');
        var $headings = $('.entry-content h2, .entry-content h3');

        if ($headings.length === 0 || $tocContainer.length === 0) {
            $('.toc-container').hide();
            return;
        }

        var $ul = $('<ul></ul>');

        $headings.each(function (index) {
            var $heading = $(this);
            var id = 'heading-' + index;

            // Asignar ID si no tiene
            if (!$heading.attr('id')) {
                $heading.attr('id', id);
            } else {
                id = $heading.attr('id');
            }

            var title = $heading.text();
            var tagName = $heading.prop('tagName').toLowerCase();
            var className = tagName === 'h3' ? 'toc-h3' : 'toc-h2';

            var $li = $('<li></li>');
            var $a = $('<a></a>').attr('href', '#' + id).text(title).addClass(className);

            $li.append($a);
            $ul.append($li);
        });

        $tocContainer.append($ul);

        // Scroll Spy simple
        $(window).on('scroll', function () {
            var scrollPos = $(window).scrollTop();
            var offset = 100; // Offset para el header fijo si lo hubiera

            $headings.each(function () {
                var currLink = $tocContainer.find('a[href="#' + $(this).attr('id') + '"]');
                var refElement = $(this);

                if (refElement.position().top <= scrollPos + offset && refElement.position().top + refElement.height() > scrollPos + offset) {
                    $tocContainer.find('a').removeClass('active');
                    currLink.addClass('active');
                }
            });
        });
    }

    generateTOC();

    // Magic Line Effect (Ping Pong Animation)
    function initMagicLine() {
        var $nav = $('.main-navigation');
        var $line = $('<div class="magic-line"></div>');
        var $activeItem = $nav.find('.current-menu-item a');
        var $items = $nav.find('a');

        if ($nav.length === 0) return;

        $nav.append($line);
        $line.show();

        var currentLeft = 0;
        var targetLeft = 0;
        var isAnimating = false;
        var animationStart = 0;
        var startLeft = 0;
        var duration = 600;

        // Initial Position
        if ($activeItem.length) {
            var navOffset = $nav.offset().left;
            var elOffset = $activeItem.offset().left;
            var elWidth = $activeItem.outerWidth();
            var dotWidth = 8;
            currentLeft = (elOffset - navOffset) + (elWidth / 2) - (dotWidth / 2);
            targetLeft = currentLeft;

            $line.css({
                'left': currentLeft + 'px',
                'opacity': '1',
                'bottom': '-6px'
            });
        } else {
            $line.css('opacity', '0');
        }

        function animate(timestamp) {
            if (!animationStart) animationStart = timestamp;
            var progress = Math.min((timestamp - animationStart) / duration, 1);

            var ease = 1 - Math.pow(1 - progress, 4);
            var newLeft = startLeft + (targetLeft - startLeft) * ease;
            var bounceHeight = 30;
            var y = -bounceHeight * (4 * ease * (1 - ease));

            $line.css({
                'left': newLeft + 'px',
                'transform': 'translateY(' + y + 'px)'
            });

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                isAnimating = false;
                currentLeft = targetLeft;
                $line.css('transform', 'translateY(0px)');
            }
        }

        function moveLine($el) {
            if ($el.length) {
                var navOffset = $nav.offset().left;
                var elOffset = $el.offset().left;
                var elWidth = $el.outerWidth();
                var dotWidth = 8;

                var newTarget = (elOffset - navOffset) + (elWidth / 2) - (dotWidth / 2);

                if (newTarget !== targetLeft) {
                    var currentVisualLeft = parseFloat($line.css('left'));
                    startLeft = isNaN(currentVisualLeft) ? newTarget : currentVisualLeft;

                    targetLeft = newTarget;
                    animationStart = 0;
                    isAnimating = true;
                    $line.css('opacity', '1');
                    requestAnimationFrame(animate);
                }
            } else {
                $line.css('opacity', '0');
            }
        }

        // Hover Events - Only return to active when leaving entire nav
        $items.on('mouseenter', function () {
            moveLine($(this));
        });

        $nav.on('mouseleave', function () {
            if ($activeItem.length) {
                moveLine($activeItem);
            } else {
                $line.css('opacity', '0');
            }
        });

        // Update on resize
        $(window).on('resize', function () {
            if ($activeItem.length) {
                var navOffset = $nav.offset().left;
                var elOffset = $activeItem.offset().left;
                var elWidth = $activeItem.outerWidth();
                var dotWidth = 8;
                var pos = (elOffset - navOffset) + (elWidth / 2) - (dotWidth / 2);

                $line.css('left', pos + 'px');
                currentLeft = pos;
                targetLeft = pos;
            }
        });
    }

    initMagicLine();

})(jQuery);
