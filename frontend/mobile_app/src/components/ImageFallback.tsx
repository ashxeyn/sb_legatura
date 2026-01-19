import React, { useState } from 'react';
import { Image, ImageStyle, StyleProp } from 'react-native';

interface ImageFallbackProps {
  uri?: string | null;
  defaultImage: any;
  style?: StyleProp<ImageStyle>;
  resizeMode?: any;
}

export default function ImageFallback({ uri, defaultImage, style, resizeMode }: ImageFallbackProps) {
  const [failed, setFailed] = useState(false);

  const source = !failed && uri ? { uri } : defaultImage;

  return (
    <Image
      source={source}
      style={style}
      resizeMode={resizeMode}
      onError={() => setFailed(true)}
    />
  );
}
