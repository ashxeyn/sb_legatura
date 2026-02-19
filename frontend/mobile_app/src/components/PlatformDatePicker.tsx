import React from 'react';
import { View, TextInput, Platform, TouchableOpacity, Text } from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import { formatDate } from '../utils/roleFormUtils';

type Props = {
  value?: string; // YYYY-MM-DD
  onChange: (val: string) => void;
  maximumDate?: Date;
};

export default function PlatformDatePicker({ value, onChange, maximumDate }: Props) {
  if (Platform.OS === 'web') {
    return (
      <View>
        <TextInput
          value={value || ''}
          placeholder="YYYY-MM-DD"
          onChangeText={(v) => onChange(v)}
          style={{ padding: 12, borderWidth: 1, borderColor: '#E5E5E5', borderRadius: 8 }}
        />
        <TouchableOpacity onPress={() => onChange(value || '')} style={{ marginTop: 8 }}>
          <Text style={{ color: '#EC7E00' }}>Set</Text>
        </TouchableOpacity>
      </View>
    );
  }

  // Native platforms: use native date picker
  const dateValue = value ? new Date(value) : new Date();
  return (
    <DateTimePicker
      value={dateValue}
      mode="date"
      display="spinner"
      maximumDate={maximumDate}
      onChange={(_e, d) => {
        if (d) onChange(formatDate(d));
      }}
    />
  );
}
