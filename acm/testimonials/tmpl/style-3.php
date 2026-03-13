<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - AutoRent Testimonials Style 3: Full-Width Dark Theme
 * ------------------------------------------------------------------------
 * ACM layout: Full-width section with background image, dark overlay, and testimonials.
 * Slick slider implementation with dots navigation and configurable background.
 *
 * Extra Fields used:
 *   testimonial-item.text    (testimonial content)
 *   testimonial-item.author  (author name)
 *   testimonial-item.image   (optional background image - configurable)
 *
 * Background: Via ACM field testimonial-item.image (configurable per testimonial)
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

$count = $helper->getRows('testimonial-item.text');
$uid   = 'ar-testimonials-' . $module->id;

// Get background image from the basic settings field
$bgImage = $helper->get('background-image');
if (empty($bgImage)) {
    // Fallback to first testimonial image if available
    $bgImage = $helper->get('testimonial-item.image', 0);
    if (empty($bgImage)) {
        $bgImage = '/images/testimonials-bg.jpg'; // Default fallback
    }
}
?>

<style>
/* ═══ AutoRent Testimonials Full-Width Dark Theme (style-3) ══════════════ */
#<?php echo $uid; ?> {
	position: relative;
	padding: 40px 20px;
	min-height: 500px;
	display: flex;
	overflow: hidden;
}
#<?php echo $uid; ?> .ar-testimonials-bg {
	position: absolute;
	inset: 0;
	width: 100%;
	height: 100%;
	object-fit: cover;
	object-position: center 80%;
}
#<?php echo $uid; ?> .ar-testimonials-overlay {
	position: absolute;
	inset: 0;
	background: rgba(0, 0, 0, 0.6);
}
#<?php echo $uid; ?> .ar-testimonials-gradient {
	position: absolute;
	inset: 0;
	opacity: 0.25;
	background-image: radial-gradient(circle at 50% 0%, rgba(255, 107, 0, 0.52) 0%, transparent 100%);
}
#<?php echo $uid; ?> .ar-testimonials-content {
	position: relative;
	z-index: 10;
	max-width: 1440px;
	margin: 0 auto;
	padding: 0 20px;
	display: flex;
	flex-direction: column;
	justify-content: center;
	min-height: 100%;
}
#<?php echo $uid; ?> .ar-testimonials-slider {
	position: relative;
}
#<?php echo $uid; ?> .ar-testimonial-slide {
	padding: 0 16px;
}
#<?php echo $uid; ?> .ar-testimonial-content {
	text-align: center;
	padding: 20px;
}
#<?php echo $uid; ?> .ar-testimonial-quote {
	font-weight: 500;
	font-size: 16px;
	line-height: 1.4;
	color: #fff;
	max-width: 48rem; /* max-w-3xl */
	margin: 0 auto 24px;
}
#<?php echo $uid; ?> .ar-testimonial-author {
	font-size: 14px;
	font-weight: 700;
	color: rgba(255, 255, 255, 0.9);
	letter-spacing: 0.5px;
}
/* Responsive typography */
@media (min-width: 640px) {
	#<?php echo $uid; ?> .ar-testimonial-quote { font-size: 20px; margin-bottom: 32px; }
}
@media (min-width: 768px) {
	#<?php echo $uid; ?> .ar-testimonial-quote { font-size: 24px; margin-bottom: 40px; }
	#<?php echo $uid; ?> .ar-testimonial-author { font-size: 16px; }
}
@media (min-width: 1024px) {
	#<?php echo $uid; ?> .ar-testimonial-quote { font-size: 28px; }
	#<?php echo $uid; ?> .ar-testimonial-author { font-size: 18px; }
}
/* Slider dots navigation */
#<?php echo $uid; ?> .ar-testimonials-dots {
	display: flex;
	justify-content: center;
	align-items: center;
	gap: 12px;
	margin-top: 32px;
}
#<?php echo $uid; ?> .ar-testimonials-dot {
	width: 12px;
	height: 12px;
	border-radius: 50%;
	background: rgba(255, 255, 255, 0.5);
	border: none;
	cursor: pointer;
	transition: all 0.3s ease;
}
#<?php echo $uid; ?> .ar-testimonials-dot:hover {
	background: rgba(255, 255, 255, 0.7);
}
#<?php echo $uid; ?> .ar-testimonials-dot.active {
	width: 32px;
	border-radius: 6px;
	background: #FE5001;
}
/* Fade transition for slides */
#<?php echo $uid; ?> .ar-testimonials-slider {
	position: relative;
}
#<?php echo $uid; ?> .ar-testimonial-slide {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	opacity: 0;
	transition: opacity 0.6s ease;
	pointer-events: none;
}
#<?php echo $uid; ?> .ar-testimonial-slide.active-slide {
	position: relative;
	opacity: 1;
	pointer-events: auto;
}
/* ══════════════════════════════════════════════════════════════════════ */
</style>

<div id="<?php echo $uid; ?>" class="acm-testimonials style-3">
	<img src="<?php echo htmlspecialchars($bgImage); ?>" alt="Testimonials Background" class="ar-testimonials-bg">
	<div class="ar-testimonials-overlay"></div>
	<div class="ar-testimonials-gradient"></div>
	
	<div class="ar-testimonials-content">
		<?php 
		// Get autoplay speed - the field 'autoplay-speed' works correctly
		$autoplaySpeed = $helper->get('autoplay-speed');
		// Ensure it's a valid integer
		$autoplaySpeed = (int)$autoplaySpeed;
		if (empty($autoplaySpeed) || $autoplaySpeed <= 0) {
			$autoplaySpeed = 4000; // Fallback to default
		}
		?>
		<div class="ar-testimonials-slider" data-autoplay-speed="<?php echo (int)$autoplaySpeed; ?>" data-slick='{"dots": true, "arrows": false, "autoplay": true, "autoplaySpeed": <?php echo (int)$autoplaySpeed; ?>, "fade": true}' data-debug-autoplay="<?php echo htmlspecialchars($autoplaySpeed); ?>">
			<?php for ($i = 0; $i < $count; $i++): 
				$text = $helper->get('testimonial-item.text', $i);
				$author = $helper->get('testimonial-item.author', $i);
				if (empty($text)) continue;
			?>
			<div class="ar-testimonial-slide">
				<div class="ar-testimonial-content">
					<p class="ar-testimonial-quote"><?php echo $text; ?></p>
					<p class="ar-testimonial-author"><?php echo $author; ?></p>
				</div>
			</div>
			<?php endfor; ?>
		</div>
		
		<div class="ar-testimonials-dots" id="<?php echo $uid; ?>-dots">
			<?php for ($i = 0; $i < $count; $i++): ?>
			<button class="ar-testimonials-dot<?php echo $i === 0 ? ' active' : ''; ?>" 
			        data-index="<?php echo $i; ?>"
			        aria-label="Go to testimonial <?php echo ($i + 1); ?>"></button>
			<?php endfor; ?>
		</div>
	</div>
</div>

<script>
(function() {
	// Simple slider implementation without external dependencies
	function initTestimonialsSlider(containerId) {
		const container = document.getElementById(containerId);
		if (!container) return;
		
		// Find the actual slider element within the container
		const slider = container.querySelector('.ar-testimonials-slider');
		if (!slider) return;
		
		const slides = container.querySelectorAll('.ar-testimonial-slide');
		const dots = container.querySelectorAll('.ar-testimonials-dot');
		let currentIndex = 0;
		let autoPlayTimer;
		
		if (slides.length === 0) return;
		
		function showSlide(index) {
			// Fade out all slides
			slides.forEach(slide => slide.classList.remove('active-slide'));
			// Remove active class from all dots
			dots.forEach(dot => dot.classList.remove('active'));
			
			// Fade in current slide
			slides[index].classList.add('active-slide');
			// Add active class to current dot
			if (dots[index]) dots[index].classList.add('active');
			
			currentIndex = index;
		}
		
		function nextSlide() {
			const nextIndex = (currentIndex + 1) % slides.length;
			showSlide(nextIndex);
		}
		
		function goToSlide(index) {
			showSlide(index);
			resetAutoPlay();
		}
		
		function resetAutoPlay() {
			clearInterval(autoPlayTimer);
			const autoplaySpeedAttr = slider.getAttribute('data-autoplay-speed');
			const autoplaySpeed = parseInt(autoplaySpeedAttr) || 4000;
			console.log('Autoplay speed:', autoplaySpeed, 'from attribute:', autoplaySpeedAttr, 'typeof:', typeof autoplaySpeedAttr);
			autoPlayTimer = setInterval(nextSlide, autoplaySpeed);
		}
		
		// Initialize
		showSlide(0);
		resetAutoPlay();
		
		// Add click events to dots
		dots.forEach((dot, index) => {
			dot.addEventListener('click', () => goToSlide(index));
		});
		
		// Pause on hover
		container.addEventListener('mouseenter', () => clearInterval(autoPlayTimer));
		container.addEventListener('mouseleave', resetAutoPlay);
	}
	
	// Initialize slider when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => initTestimonialsSlider('<?php echo $uid; ?>'));
	} else {
		initTestimonialsSlider('<?php echo $uid; ?>');
	}
})();
</script>