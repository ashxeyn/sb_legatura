import React, { useState, useEffect } from 'react';
import { Image, ImageStyle, StyleProp } from 'react-native';
import { api_config } from '../config/api';

interface ImageFallbackProps {
  uri?: string | null;
  defaultImage: any;
  style?: StyleProp<ImageStyle>;
  resizeMode?: any;
}

export default function ImageFallback({ uri, defaultImage, style, resizeMode }: ImageFallbackProps) {
  const [failed, setFailed] = useState(false);

  // Reset failed state whenever the URI changes so new valid URLs are always attempted
  useEffect(() => {
    setFailed(false);
  }, [uri]);

  const normalizeUri = (path?: string | null) => {
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/api/files/')) return `${api_config.base_url}${path}`;
    if (path.startsWith('/')) return `${api_config.base_url}/api/files${path}`;
    return `${api_config.base_url}/api/files/${path}`;
  };

  const normalizedUri = normalizeUri(uri);
  const source = !failed && normalizedUri ? { uri: normalizedUri } : defaultImage;

  return (
    <Image
      source={source}
      style={style}
      resizeMode={resizeMode}
      onError={() => setFailed(true)}
    />
  );
}
