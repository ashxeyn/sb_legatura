/**
 * Drop-in replacement for ScrollView that automatically wraps content in
 * KeyboardAvoidingView so the keyboard never overlaps inputs.
 *
 * Usage: just swap <ScrollView> for <KeyboardAwareScrollView> — all
 * ScrollView props pass through unchanged.
 */
import React from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  ScrollViewProps,
  StyleProp,
  ViewStyle,
  StyleSheet,
} from 'react-native';

type KeyboardAwareScrollViewProps = ScrollViewProps & {
  keyboardOffset?: number;
  style?: StyleProp<ViewStyle>;
  contentContainerStyle?: ScrollViewProps['contentContainerStyle'];
};

export default function KeyboardAwareScrollView({ style, contentContainerStyle, children, keyboardOffset = 0, ...rest }: KeyboardAwareScrollViewProps) {
  return (
    <KeyboardAvoidingView
      style={styles.kav}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      keyboardVerticalOffset={keyboardOffset}
    >
      <ScrollView
        style={style}
        contentContainerStyle={contentContainerStyle}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
        {...rest}
      >
        {children}
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  kav: {
    flex: 1,
  },
});
