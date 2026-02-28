import AsyncStorage from '@react-native-async-storage/async-storage';

const STORAGE_KEYS = {
  USER_DATA: '@legatura_user_data',
  AUTH_TOKEN: '@legatura_auth_token',
  IS_AUTHENTICATED: '@legatura_is_authenticated',
  // PINNED_PROJECT removed
};

export class storage_service {
  // cached user to allow synchronous reads during render
  static _cachedUser: any = null;
  // Save user data and auth state
  static async save_user_data(user_data: any): Promise<boolean> {
    try {
      await AsyncStorage.setItem(STORAGE_KEYS.USER_DATA, JSON.stringify(user_data));
      await AsyncStorage.setItem(STORAGE_KEYS.IS_AUTHENTICATED, 'true');
      // update cached copy
      storage_service._cachedUser = user_data;
      console.log('User data saved to storage');
      return true;
    } catch (error) {
      console.error('Error saving user data:', error);
      return false;
    }
  }

  // Get stored user data
  static async get_user_data(): Promise<any | null> {
    try {
      const user_data_string = await AsyncStorage.getItem(STORAGE_KEYS.USER_DATA);
      if (user_data_string) {
        const user_data = JSON.parse(user_data_string);
        // cache for synchronous access
        storage_service._cachedUser = user_data;
        console.log('User data retrieved from storage:', user_data.username);
        return user_data;
      }
      return null;
    } catch (error) {
      console.error('Error retrieving user data:', error);
      return null;
    }
  }

  // Synchronous read of the last-cached user data (may be null)
  static get_user_data_sync(): any | null {
    return storage_service._cachedUser || null;
  }

  // Check if user is authenticated
  static async is_authenticated(): Promise<boolean> {
    try {
      const is_auth = await AsyncStorage.getItem(STORAGE_KEYS.IS_AUTHENTICATED);
      return is_auth === 'true';
    } catch (error) {
      console.error('Error checking authentication:', error);
      return false;
    }
  }

  // Clear all user data (logout)
  static async clear_user_data(): Promise<boolean> {
    try {
      await AsyncStorage.multiRemove([
        STORAGE_KEYS.USER_DATA,
        STORAGE_KEYS.AUTH_TOKEN,
        STORAGE_KEYS.IS_AUTHENTICATED,
      ]);
      console.log('User data cleared from storage');
      return true;
    } catch (error) {
      console.error('Error clearing user data:', error);
      return false;
    }
  }

  // Save auth token
  static async save_auth_token(token: string): Promise<boolean> {
    try {
      await AsyncStorage.setItem(STORAGE_KEYS.AUTH_TOKEN, token);
      return true;
    } catch (error) {
      console.error('Error saving auth token:', error);
      return false;
    }
  }

  // Get auth token
  static async get_auth_token(): Promise<string | null> {
    try {
      return await AsyncStorage.getItem(STORAGE_KEYS.AUTH_TOKEN);
    } catch (error) {
      console.error('Error retrieving auth token:', error);
      return null;
    }
  }

  // Save pinned project for a specific user
  // NOTE: pinned project helpers removed
}
