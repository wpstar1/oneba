// 모바일 메뉴 토글
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const dropdowns = document.querySelectorAll('.dropdown');

    // 모바일 메뉴 토글
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
    }

    // 모바일에서 드롭다운 클릭 처리
    dropdowns.forEach(dropdown => {
        const link = dropdown.querySelector('a');
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            }
        });
    });

    // 스크롤 애니메이션
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // 애니메이션 대상 요소들
    const animateElements = document.querySelectorAll('.about-card, .casino-card, .game-card, .travel-card, .board-section');
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });

    // 스무스 스크롤
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // 헤더 스크롤 효과
    let lastScroll = 0;
    const header = document.querySelector('.header');

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 100) {
            header.style.padding = '10px 0';
            header.style.boxShadow = '0 5px 30px rgba(0, 0, 0, 0.8)';
        } else {
            header.style.padding = '20px 0';
            header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.5)';
        }

        lastScroll = currentScroll;
    });

    // 이미지 레이지 로딩
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));

    // 카운터 애니메이션
    function animateCounter(element, target, duration = 2000) {
        let start = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(() => {
            start += increment;
            if (start >= target) {
                element.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(start).toLocaleString();
            }
        }, 16);
    }

    // 통계 카운터 (필요시 사용)
    const counters = document.querySelectorAll('.counter');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.dataset.target);
                animateCounter(entry.target, target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => counterObserver.observe(counter));

    // 폼 검증 (게시판 페이지용)
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#ff4444';

                    setTimeout(() => {
                        input.style.borderColor = '';
                    }, 2000);
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('모든 필수 항목을 입력해주세요.');
            }
        });
    });

    // 툴팁 기능
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';

            setTimeout(() => tooltip.classList.add('show'), 10);

            this.addEventListener('mouseleave', function() {
                tooltip.classList.remove('show');
                setTimeout(() => tooltip.remove(), 300);
            }, { once: true });
        });
    });

    // 검색 기능 (커뮤니티용)
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const items = document.querySelectorAll('.searchable-item');

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // 좋아요 버튼 (커뮤니티용)
    const likeButtons = document.querySelectorAll('.like-button');
    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.classList.toggle('liked');
            const count = this.querySelector('.like-count');
            if (count) {
                let currentCount = parseInt(count.textContent);
                count.textContent = this.classList.contains('liked') ? currentCount + 1 : currentCount - 1;
            }
        });
    });

    // 북마크 기능
    const bookmarkButtons = document.querySelectorAll('.bookmark-button');
    bookmarkButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.classList.toggle('bookmarked');
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fas');
                icon.classList.toggle('far');
            }
        });
    });

    // 이미지 모달
    const modalImages = document.querySelectorAll('.modal-image');
    modalImages.forEach(img => {
        img.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.className = 'image-modal';
            modal.innerHTML = `
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <img src="${this.src}" alt="${this.alt}">
                    <button class="modal-close"><i class="fas fa-times"></i></button>
                </div>
            `;
            document.body.appendChild(modal);

            setTimeout(() => modal.classList.add('show'), 10);

            modal.querySelector('.modal-close').addEventListener('click', () => {
                modal.classList.remove('show');
                setTimeout(() => modal.remove(), 300);
            });

            modal.querySelector('.modal-overlay').addEventListener('click', () => {
                modal.classList.remove('show');
                setTimeout(() => modal.remove(), 300);
            });
        });
    });

    // 탭 기능
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.dataset.tab;

            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            this.classList.add('active');
            document.getElementById(target)?.classList.add('active');
        });
    });

    // 페이지네이션
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!this.classList.contains('disabled')) {
                paginationLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // 뒤로가기 버튼
    const backButtons = document.querySelectorAll('.back-button');
    backButtons.forEach(button => {
        button.addEventListener('click', () => {
            window.history.back();
        });
    });

    // 공유 기능
    const shareButtons = document.querySelectorAll('.share-button');
    shareButtons.forEach(button => {
        button.addEventListener('click', async function() {
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: document.title,
                        text: '카지노 월드 - 세계 카지노 정보',
                        url: window.location.href
                    });
                } catch (err) {
                    console.log('공유 실패:', err);
                }
            } else {
                // 폴백: URL 복사
                navigator.clipboard.writeText(window.location.href);
                alert('링크가 클립보드에 복사되었습니다!');
            }
        });
    });

    // 다크모드 토글 (선택사항)
    const darkModeToggle = document.querySelector('.dark-mode-toggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('light-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('light-mode') ? 'false' : 'true');
        });

        // 저장된 설정 불러오기
        if (localStorage.getItem('darkMode') === 'false') {
            document.body.classList.add('light-mode');
        }
    }

    // 프린트 기능
    const printButtons = document.querySelectorAll('.print-button');
    printButtons.forEach(button => {
        button.addEventListener('click', () => {
            window.print();
        });
    });

    // 스크롤 탑 버튼
    const scrollTopButton = document.createElement('button');
    scrollTopButton.className = 'scroll-top-button';
    scrollTopButton.innerHTML = '<i class="fas fa-chevron-up"></i>';
    document.body.appendChild(scrollTopButton);

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollTopButton.classList.add('show');
        } else {
            scrollTopButton.classList.remove('show');
        }
    });

    scrollTopButton.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // 로딩 스피너 제거
    window.addEventListener('load', () => {
        const loader = document.querySelector('.loader');
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(() => loader.remove(), 300);
        }
    });

    console.log('카지노 월드 웹사이트가 로드되었습니다.');
});