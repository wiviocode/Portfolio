import * as React from 'react';
import * as Dialog from '@radix-ui/react-dialog';
import { motion, AnimatePresence } from 'framer-motion';
import { cn } from '../lib/utils';

interface LightboxProps {
  isOpen: boolean;
  onClose: () => void;
  images: Array<{ src: string; alt: string }>;
  currentIndex: number;
  onNext: () => void;
  onPrev: () => void;
}

export const Lightbox = ({
  isOpen,
  onClose,
  images,
  currentIndex,
  onNext,
  onPrev,
}: LightboxProps) => {
  // Handle keyboard navigation
  React.useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (!isOpen) return;
      
      switch (e.key) {
        case 'ArrowLeft':
          onPrev();
          break;
        case 'ArrowRight':
          onNext();
          break;
        case 'Escape':
          onClose();
          break;
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [isOpen, onNext, onPrev, onClose]);

  // Handle touch events
  const touchStart = React.useRef(0);
  const handleTouchStart = (e: React.TouchEvent) => {
    touchStart.current = e.touches[0].clientX;
  };

  const handleTouchEnd = (e: React.TouchEvent) => {
    const touchEnd = e.changedTouches[0].clientX;
    const diff = touchStart.current - touchEnd;

    if (Math.abs(diff) > 50) {
      if (diff > 0) {
        onNext();
      } else {
        onPrev();
      }
    }
  };

  return (
    <Dialog.Root open={isOpen} onOpenChange={onClose}>
      <AnimatePresence>
        {isOpen && (
          <Dialog.Portal forceMount>
            <Dialog.Overlay asChild>
              <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                className="fixed inset-0 z-50 bg-black/90"
              />
            </Dialog.Overlay>
            <Dialog.Content
              onTouchStart={handleTouchStart}
              onTouchEnd={handleTouchEnd}
              className="fixed inset-0 z-50 flex items-center justify-center"
            >
              <motion.div
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.95 }}
                transition={{ duration: 0.2 }}
                className="relative w-full h-full flex items-center justify-center"
              >
                <button
                  onClick={onPrev}
                  className="absolute left-4 z-50 p-2 text-white hover:text-accent transition-colors"
                >
                  ←
                </button>
                <img
                  src={images[currentIndex].src}
                  alt={images[currentIndex].alt}
                  className="max-w-[90%] max-h-[90vh] object-contain"
                />
                <button
                  onClick={onNext}
                  className="absolute right-4 z-50 p-2 text-white hover:text-accent transition-colors"
                >
                  →
                </button>
                <button
                  onClick={onClose}
                  className="absolute top-4 right-4 z-50 p-2 text-white hover:text-accent transition-colors"
                >
                  ×
                </button>
              </motion.div>
            </Dialog.Content>
          </Dialog.Portal>
        )}
      </AnimatePresence>
    </Dialog.Root>
  );
}; 