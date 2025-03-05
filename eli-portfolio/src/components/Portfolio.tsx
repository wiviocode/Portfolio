import * as React from 'react';
import { Image } from './Image';
import { Lightbox } from './Lightbox';
import { motion } from 'framer-motion';
import { useInView } from 'react-intersection-observer';

interface PortfolioProps {
  images: Array<{
    src: string;
    alt: string;
  }>;
}

export const Portfolio = ({ images }: PortfolioProps) => {
  const [lightboxOpen, setLightboxOpen] = React.useState(false);
  const [currentIndex, setCurrentIndex] = React.useState(0);
  
  const handleImageClick = (index: number) => {
    setCurrentIndex(index);
    setLightboxOpen(true);
  };

  const handleNext = () => {
    setCurrentIndex((prev) => (prev + 1) % images.length);
  };

  const handlePrev = () => {
    setCurrentIndex((prev) => (prev - 1 + images.length) % images.length);
  };

  return (
    <>
      <div className="columns-1 md:columns-2 lg:columns-3 gap-4 p-4">
        {images.map((image, index) => (
          <PortfolioItem
            key={image.src}
            image={image}
            onClick={() => handleImageClick(index)}
          />
        ))}
      </div>

      <Lightbox
        isOpen={lightboxOpen}
        onClose={() => setLightboxOpen(false)}
        images={images}
        currentIndex={currentIndex}
        onNext={handleNext}
        onPrev={handlePrev}
      />
    </>
  );
};

interface PortfolioItemProps {
  image: {
    src: string;
    alt: string;
  };
  onClick: () => void;
}

const PortfolioItem = ({ image, onClick }: PortfolioItemProps) => {
  const { ref, inView } = useInView({
    triggerOnce: true,
    threshold: 0.1,
  });

  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 20 }}
      animate={inView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.5 }}
      className="mb-4 break-inside-avoid"
    >
      <Image
        src={image.src}
        alt={image.alt}
        onClick={onClick}
        className="w-full rounded-lg overflow-hidden"
      />
    </motion.div>
  );
}; 