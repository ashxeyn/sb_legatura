/**
 * Pusher Configuration for Real-time Messaging
 * 
 * This file configures Pusher client for React Native to enable
 * real-time message delivery via WebSockets.
 */

// Pusher credentials from Laravel backend
export const pusher_config = {
  app_id: '2112120',
  app_key: 'c8539eba4bad9ec5e663',
  cluster: 'ap1',
  encrypted: true,
  forceTLS: true,
  authEndpoint: 'http://192.168.254.112:8086/broadcasting/auth',
};

/**
 * Initialize Pusher client for React Native
 * 
 * Note: For React Native, we use pusher-js/react-native instead of the web version.
 * Install with: npm install pusher-js @react-native-community/netinfo
 */
export const initPusher = async (authToken: string) => {
  try {
    // Dynamic import to avoid loading Pusher before it's installed
    const Pusher = require('pusher-js/react-native');
    
    const pusher = new Pusher(pusher_config.app_key, {
      cluster: pusher_config.cluster,
      encrypted: pusher_config.encrypted,
      forceTLS: pusher_config.forceTLS,
      authEndpoint: pusher_config.authEndpoint,
      auth: {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json',
        },
      },
      // Enable logging for debugging
      enabledTransports: ['ws', 'wss'],
    });

    // Connection state listeners
    pusher.connection.bind('connected', () => {
      console.log('Pusher: Connected successfully');
    });

    pusher.connection.bind('disconnected', () => {
      console.log('Pusher: Disconnected');
    });

    pusher.connection.bind('error', (err: any) => {
      console.error('Pusher: Connection error', err);
    });

    return pusher;
  } catch (error) {
    console.error('Failed to initialize Pusher:', error);
    return null;
  }
};

/**
 * Subscribe to user's private chat channel
 * 
 * @param pusher - Pusher instance
 * @param userId - Current user's ID
 * @param onMessage - Callback when new message arrives
 * @returns Channel instance or null
 */
export const subscribeToChatChannel = (
  pusher: any,
  userId: number,
  onMessage: (message: any) => void
) => {
  try {
    const channelName = `private-chat.${userId}`;
    const channel = pusher.subscribe(channelName);

    channel.bind('pusher:subscription_succeeded', () => {
      console.log(`Pusher: Subscribed to ${channelName}`);
    });

    channel.bind('pusher:subscription_error', (status: any) => {
      console.error(`Pusher: Subscription failed for ${channelName}`, status);
    });

    // Listen for new messages
    channel.bind('message.sent', (data: any) => {
      console.log('Pusher: New message received', data);
      onMessage(data);
    });

    return channel;
  } catch (error) {
    console.error('Failed to subscribe to chat channel:', error);
    return null;
  }
};

/**
 * Unsubscribe from a channel
 */
export const unsubscribeFromChannel = (pusher: any, channelName: string) => {
  try {
    pusher.unsubscribe(channelName);
    console.log(`Pusher: Unsubscribed from ${channelName}`);
  } catch (error) {
    console.error('Failed to unsubscribe:', error);
  }
};

/**
 * Disconnect Pusher
 */
export const disconnectPusher = (pusher: any) => {
  try {
    if (pusher) {
      pusher.disconnect();
      console.log('Pusher: Disconnected');
    }
  } catch (error) {
    console.error('Failed to disconnect Pusher:', error);
  }
};
