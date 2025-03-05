import React from 'react';
import { LazyLoadImage } from 'react-lazy-load-image-component';

interface ImageProps {
  src: string;
  alt: string;
  width?: number;
  height?: number;
  className?: string;
  onClick?: () => void;
}

export const Image = ({
  src,
  alt,
  width,
  height,
  className = "",
  onClick,
}: ImageProps) => {
  return (
    <div className={`relative ${className}`}>
      <LazyLoadImage
        src={src}
        alt={alt}
        width={width}
        height={height}
        effect="opacity"
        onClick={onClick}
        className="w-full"
        wrapperClassName="w-full h-full"
      />
    </div>
  );
}; 