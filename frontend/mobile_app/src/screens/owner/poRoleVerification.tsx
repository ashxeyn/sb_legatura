import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  Image,
  ScrollView,
  Alert,
  Modal,
  FlatList
} from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { PropertyOwnerPersonalInfo as PersonalInfo } from './personalInfo';
import { AccountInfo } from './accountSetup';
import { valid_id, auth_service } from '../../services/auth_service';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import AsyncStorage from '@react-native-async-storage/async-storage';

const LEGATURA_TOS = `LEGATURA TERMS OF SERVICE
Last Updated: March 2026

1. Acceptance of Terms and Platform Definition

By accessing or using the Legatura platform, you agree to be bound by these Terms of Service. Legatura operates strictly as an information and matching intermediary and a digital workflow manager. Legatura is an Information Technology (IT) software provider; it is not a construction firm, an engineering firm, a bank, or an e-wallet.

2. Limitation of Liability for Construction and Post-Project Defects

2.1. No Construction Guarantee: Legatura facilitates the connection between Property Owners and Contractors. We do not oversee physical construction, supply materials, or manage on-site labor.

2.2. Article 1723 Compliance: In strict accordance with Article 1723 of the Civil Code of the Philippines, the fifteen-year (15-year) legal liability for the collapse of a building or structure due to defects in construction, the use of inferior materials, or violations of the terms of the contract rests entirely and exclusively upon the licensed Engineer, Architect, or Contractor who executed the project.

2.3. Indemnification: The Property Owner agrees that Legatura, its administrators, and its developers shall bear zero legal or financial liability for any structural failure, property damage, personal injury, or subsequent loss resulting from the physical construction activities of the hired Contractor.

3. Verification and Account Accountability

3.1. Document Authenticity: Users are required to submit valid documentation, including but not limited to, Philippine Contractors Accreditation Board (PCAB) licenses, DTI/SEC registrations, Local Government Business Permits, and Valid Government IDs.

3.2. Platform Disclaimer: While Legatura's administration cross-references these documents against respective national and local government portals, the platform does not guarantee the real-world operational competence of the Contractor.

4. Financial Transactions and Tax Liabilities

4.1. Non-Financial Institution: Legatura acts as a milestone ledger. The platform does not hold, escrow, or transmit fiat currency. All financial transfers occur externally via the users' respective banking institutions.

4.2. Tax Obligations: Users are solely responsible for compliance with the National Internal Revenue Code (R.A. 8424). Legatura is not a Withholding Agent. The issuance of Official Receipts (OR) and the deduction of applicable withholding taxes and Value-Added Tax (VAT) remain the direct responsibility of the transacting Property Owner and Contractor.

5. Subscription Plans and Bidding Allocation

5.1. Plan-Based Bid Allocation: Contractors may subscribe to a subscription plan (e.g., Bronze, Silver, Gold) to receive a designated number of project bids. Each plan grants a fixed starting number of bids as defined at the time of subscription.

5.2. Non-Cumulative Bid Balance on Plan Change: When a Contractor upgrades or changes from one subscription plan to another (e.g., from Bronze to Silver), the bid count resets to the starting allocation of the newly subscribed plan. Remaining unused bids from the previous plan do not carry over or accumulate with the new plan's allocation.

5.3. Plan Cancellation and No-Refund Policy: A Contractor may cancel their active subscription plan at any time during the plan's duration. However, all subscription payments are final and non-refundable. Upon cancellation, the Contractor will retain access to the benefits of the current plan, including any remaining bids, until the plan's original expiration date. No pro-rated refunds or credits will be issued for the unused portion of the subscription period.

5.4. Plan Continuation Until Expiry: A cancelled plan remains active and fully functional until its scheduled end date. The Contractor may continue to use their remaining bids and plan features during this period. Once the plan expires, the account will revert to its default non-subscribed state unless a new plan is purchased.

6. Dispute Resolution and Arbitration

6.1. System Audit Trails: In the event of a dispute, Legatura will provide an immutable export of the user activity logs and milestone items to serve as electronic evidence under the Electronic Commerce Act of 2000 (R.A. 8792).

6.2. Escalation: If a project is placed on "Halt" and parties cannot reach a settlement through the platform, the deadlock must be escalated to the Construction Industry Arbitration Commission (CIAC) pursuant to Executive Order No. 1008. Legatura administrators will not act as technical judges or arbitrators for structural disputes.

7. Intellectual Property (Blueprints and Designs)

As per the Intellectual Property Code of the Philippines (R.A. 8293), all architectural designs, blueprints, and proprietary documents uploaded to the platform's project files module remain the exclusive intellectual property of the original creator.

8. Artificial Intelligence (AI) Decision Support Disclaimer

The Legatura platform utilizes an AI-driven delay prediction module. This feature is architected strictly as a Decision Support System (DSS) utilizing historical analytics and weather patterns. It generates a probability, not a deterministic guarantee. Legatura assumes no legal liability for project delays or financial losses incurred if a user relies solely on the system's delay probability metrics.

9. Data Retention and Privacy

In compliance with the Data Privacy Act of 2012 (R.A. 10173), user data is processed to establish contractual necessities. In the event of account deletion, Legatura reserves the right to retain specific contractual audit trails, system logs, and verified IDs for a legally mandated period to protect users against post-project liabilities and to establish legal claims.`;

interface VerificationScreenProps {
  onBackPress: () => void;
  onComplete: (verificationInfo: VerificationInfo) => void;
  personalInfo: PersonalInfo;
  accountInfo: AccountInfo;
  validIds: valid_id[];
  initialData?: VerificationInfo | null;
}

export interface VerificationInfo {
  valid_id_id: number;
  idTypeName: string;
  idFrontImage?: string;
  idBackImage?: string;
  policeClearanceImage?: string;
}

export default function VerificationScreen({ onBackPress, onComplete, personalInfo, accountInfo, validIds, initialData }: VerificationScreenProps) {
  const [localValidIds, setLocalValidIds] = useState<valid_id[]>(validIds || []);
  const [isLoadingIds, setIsLoadingIds] = useState(false);

  // Log validIds for debugging
  useEffect(() => {
    console.log('VerificationScreen - validIds prop:', validIds);
    console.log('VerificationScreen - validIds length:', validIds?.length || 0);
    
    // If validIds prop is empty, try to load them directly
    if (!validIds || validIds.length === 0) {
      console.log('Valid IDs not provided, attempting to load from API...');
      loadValidIds();
    } else {
      setLocalValidIds(validIds);
    }
  }, [validIds]);

  const loadValidIds = async () => {
    try {
      setIsLoadingIds(true);
      const response = await auth_service.get_signup_form_data();
      console.log('Full API response:', JSON.stringify(response, null, 2));
      
      // After normalization in auth_service, response.data should be the actual data object
      const validIdsData = response.data?.valid_ids;
      
      if (response.success && validIdsData && Array.isArray(validIdsData) && validIdsData.length > 0) {
        console.log('Loaded valid IDs from API:', validIdsData);
        setLocalValidIds(validIdsData);
      } else {
        console.error('Failed to load valid IDs. Response:', response);
        console.error('Valid IDs data:', validIdsData);
        console.error('Response data structure:', response.data);
      }
    } catch (error) {
      console.error('Error loading valid IDs:', error);
    } finally {
      setIsLoadingIds(false);
    }
  };

  const [selectedValidId, setSelectedValidId] = useState<valid_id | null>(null);
  const [idFrontImage, setIdFrontImage] = useState<string | null>(null);
  const [idBackImage, setIdBackImage] = useState<string | null>(null);
  const [policeClearanceImage, setPoliceClearanceImage] = useState<string | null>(null);
  const [showTermsModal, setShowTermsModal] = useState(false);
  const [hasReadTerms, setHasReadTerms] = useState(false);
  const [termsAgreed, setTermsAgreed] = useState(false);

  useEffect(() => {
    if (initialData && Object.keys(initialData).length > 0) {
      if (initialData.valid_id_id && validIds && validIds.length > 0) {
        setSelectedValidId(validIds.find(id => id.id === initialData.valid_id_id) || null);
      }
      setIdFrontImage(initialData.idFrontImage || null);
      setIdBackImage(initialData.idBackImage || null);
      setPoliceClearanceImage(initialData.policeClearanceImage || null);
    } else {
      AsyncStorage.getItem('signup_po_verification').then(cached => {
        if (cached) {
          try {
            const parsed = JSON.parse(cached);
            if (parsed.valid_id_id && validIds && validIds.length > 0) {
               setSelectedValidId(validIds.find(id => id.id === parsed.valid_id_id) || null);
            }
            if (parsed.idFrontImage) setIdFrontImage(parsed.idFrontImage);
            if (parsed.idBackImage) setIdBackImage(parsed.idBackImage);
            if (parsed.policeClearanceImage) setPoliceClearanceImage(parsed.policeClearanceImage);
          } catch (e) {}
        }
      });
    }
  }, [initialData, validIds]);

  useEffect(() => {
    const cache = {
      valid_id_id: selectedValidId?.id,
      idFrontImage,
      idBackImage,
      policeClearanceImage
    };
    const timer = setTimeout(() => {
      AsyncStorage.setItem('signup_po_verification', JSON.stringify(cache)).catch(() => {});
    }, 500);
    return () => clearTimeout(timer);
  }, [selectedValidId, idFrontImage, idBackImage, policeClearanceImage]);

  // ID Type selector states
  const [showIdTypeModal, setShowIdTypeModal] = useState(false);
  const [idTypeSearch, setIdTypeSearch] = useState('');
  const [filteredIdTypes, setFilteredIdTypes] = useState<valid_id[]>(localValidIds);

  // Update filteredIdTypes when localValidIds changes
  useEffect(() => {
    if (localValidIds && localValidIds.length > 0) {
      setFilteredIdTypes(localValidIds);
      console.log('Valid IDs loaded:', localValidIds);
    } else {
      console.warn('Valid IDs is empty or undefined:', localValidIds);
    }
  }, [localValidIds]);

  const handleIdTypeSearch = (text: string) => {
    setIdTypeSearch(text);
    if (text.trim() === '') {
      setFilteredIdTypes(localValidIds || []);
    } else {
      const filtered = (localValidIds || []).filter(id => {
        const idName = id.valid_id_name || id.name || '';
        return idName.toLowerCase().includes(text.toLowerCase());
      });
      setFilteredIdTypes(filtered);
    }
  };

  const selectIdType = (selectedId: valid_id) => {
    setSelectedValidId(selectedId);
    setIdTypeSearch('');
    setFilteredIdTypes(localValidIds || []);
    setShowIdTypeModal(false);
  };

  const openIdTypeModal = () => {
    if (!localValidIds || localValidIds.length === 0) {
      Alert.alert('Error', 'Valid IDs are not loaded. Please try again.');
      // Try to reload
      loadValidIds();
      return;
    }
    setIdTypeSearch('');
    setFilteredIdTypes(localValidIds);
    setShowIdTypeModal(true);
  };

  const requestPermission = async () => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission required', 'Please grant camera roll permissions to upload images.');
      return false;
    }
    return true;
  };

  const pickImage = async (imageType: 'front' | 'back' | 'police') => {
    const hasPermission = await requestPermission();
    if (!hasPermission) return;

    Alert.alert(
      'Select Image',
      'Choose an option',
      [
        { text: 'Camera', onPress: () => openCamera(imageType) },
        { text: 'Gallery', onPress: () => openGallery(imageType) },
        { text: 'Cancel', style: 'cancel' }
      ]
    );
  };

  const openCamera = async (imageType: 'front' | 'back' | 'police') => {
    const { status } = await ImagePicker.requestCameraPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission required', 'Please grant camera permissions to take photos.');
      return;
    }

    const MEDIA_IMAGES = ImagePicker.MediaTypeOptions.Images;

    const result = await ImagePicker.launchCameraAsync({
      mediaTypes: MEDIA_IMAGES,
      allowsEditing: true,
      aspect: [4, 3],
      quality: 0.8,
    });

    if (!result.canceled && result.assets[0]) {
      setImageByType(imageType, result.assets[0].uri);
    }
  };

  const openGallery = async (imageType: 'front' | 'back' | 'police') => {
    const MEDIA_IMAGES = ImagePicker.MediaTypeOptions.Images;

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: MEDIA_IMAGES,
      allowsEditing: true,
      aspect: [4, 3],
      quality: 0.8,
    });

    if (!result.canceled && result.assets[0]) {
      setImageByType(imageType, result.assets[0].uri);
    }
  };

  const setImageByType = (imageType: 'front' | 'back' | 'police', uri: string) => {
    switch (imageType) {
      case 'front':
        setIdFrontImage(uri);
        break;
      case 'back':
        setIdBackImage(uri);
        break;
      case 'police':
        setPoliceClearanceImage(uri);
        break;
    }
  };

  const isFormValid = () => {
    return selectedValidId !== null && idFrontImage && idBackImage && policeClearanceImage;
  };

  const handleComplete = () => {
    if (!selectedValidId) {
      Alert.alert('Error', 'Please select a valid ID type');
      return;
    }

    if (!idFrontImage || !idBackImage) {
      Alert.alert('Error', 'Please upload both front and back images of your ID');
      return;
    }

    if (!policeClearanceImage) {
      Alert.alert('Error', 'Please upload your Police Clearance image');
      return;
    }

    if (!termsAgreed) {
      Alert.alert('Terms of Service', 'Please read and acknowledge the Terms of Service before proceeding.');
      return;
    }

    const verificationInfo: VerificationInfo = {
      valid_id_id: selectedValidId.id,
      idTypeName: selectedValidId.valid_id_name || selectedValidId.name || '',
      idFrontImage,
      idBackImage,
      policeClearanceImage,
    };

    onComplete(verificationInfo);
  };

  return (
    <SafeAreaView style={styles.container}>
      <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        <View style={styles.logoContainer}>
          <Image
            source={require('../../../assets/images/logos/legatura-logo.png')}
            style={styles.logo}
            resizeMode="contain"
          />
        </View>

        <View style={styles.progressContainer}>
          <View style={styles.progressStep}>
            <View style={[styles.progressBar, styles.progressBarActive]} />
            <Text style={[styles.progressText, styles.progressTextActive]}>Personal Information</Text>
          </View>
          <View style={styles.progressStep}>
            <View style={[styles.progressBar, styles.progressBarActive]} />
            <Text style={[styles.progressText, styles.progressTextActive]}>Account Setup</Text>
          </View>
          <View style={styles.progressStep}>
            <View style={[styles.progressBar, styles.progressBarActive]} />
            <Text style={[styles.progressText, styles.progressTextActive]}>Role Verification</Text>
          </View>
          <View style={styles.progressStep}>
            <View style={[styles.progressBar, styles.progressBarActive]} />
            <Text style={[styles.progressText, styles.progressTextActive]}>Verification</Text>
          </View>
        </View>

        <View style={styles.formContainer}>
          <View style={styles.inputContainer}>
            {isLoadingIds ? (
              <View style={styles.errorContainer}>
                <Text style={styles.errorText}>Loading valid IDs...</Text>
              </View>
            ) : (!localValidIds || localValidIds.length === 0) ? (
              <View style={styles.errorContainer}>
                <Text style={styles.errorText}>Valid IDs are not loaded. Please go back and try again.</Text>
              </View>
            ) : (
            <TouchableOpacity style={styles.dropdownContainer} onPress={openIdTypeModal}>
              <View style={styles.dropdownInputWrapper}>
                  <Text style={[styles.dropdownInputText, !selectedValidId && styles.placeholderText]}>
                    {selectedValidId ? (selectedValidId.valid_id_name || selectedValidId.name) : 'Type of Valid ID'}
                </Text>
                <MaterialIcons name="keyboard-arrow-down" size={24} color="#666666" style={styles.dropdownIcon} />
              </View>
            </TouchableOpacity>
            )}
          </View>

          <View style={styles.sectionSeparator}>
            <Text style={styles.sectionTitle}>Valid ID Images</Text>
          </View>

          <View style={styles.uploadSection}>
            <Text style={styles.uploadLabel}>Valid ID - Front Side</Text>
            <TouchableOpacity
              style={styles.uploadContainer}
              onPress={() => pickImage('front')}
            >
              {idFrontImage ? (
                <Image source={{ uri: idFrontImage }} style={styles.uploadedImage} />
              ) : (
                <>
                  <MaterialIcons name="cloud-upload" size={48} color="#CCCCCC" />
                  <Text style={styles.uploadText}>Upload front image</Text>
                </>
              )}
            </TouchableOpacity>
          </View>

          <View style={styles.uploadSection}>
            <Text style={styles.uploadLabel}>Valid ID - Back Side</Text>
            <TouchableOpacity
              style={styles.uploadContainer}
              onPress={() => pickImage('back')}
            >
              {idBackImage ? (
                <Image source={{ uri: idBackImage }} style={styles.uploadedImage} />
              ) : (
                <>
                  <MaterialIcons name="cloud-upload" size={48} color="#CCCCCC" />
                  <Text style={styles.uploadText}>Upload back image</Text>
                </>
              )}
            </TouchableOpacity>
          </View>

          <View style={styles.sectionSeparator}>
            <Text style={styles.sectionTitle}>Police Clearance</Text>
          </View>

          <View style={styles.uploadSection}>
            <Text style={styles.uploadLabel}>Police Clearance Image</Text>
            <TouchableOpacity
              style={styles.uploadContainer}
              onPress={() => pickImage('police')}
            >
              {policeClearanceImage ? (
                <Image source={{ uri: policeClearanceImage }} style={styles.uploadedImage} />
              ) : (
                <>
                  <MaterialIcons name="cloud-upload" size={48} color="#CCCCCC" />
                  <Text style={styles.uploadText}>Upload image or file</Text>
                </>
              )}
            </TouchableOpacity>
          </View>

          <View style={styles.validationTextContainer}>
            <Text style={styles.validationText}>
              All files should be valid and not expired. We'll verify these documents before approving your profile.
            </Text>
          </View>

          {/* Terms of Service */}
          <View style={styles.tosCard}>
            <View style={styles.tosCardHeader}>
              <Ionicons name="document-text-outline" size={18} color="#1E3A5F" />
              <Text style={styles.tosCardTitle}>Terms of Service</Text>
            </View>
            <View style={styles.tosDivider} />
            <Text style={styles.tosCardBody}>
              Before creating your account, you must read and acknowledge the Legatura Terms of Service.
            </Text>
            <TouchableOpacity
              style={[styles.tosReadButton, termsAgreed && styles.tosReadButtonAgreed]}
              onPress={() => setShowTermsModal(true)}
            >
              <Ionicons name={termsAgreed ? 'checkmark-circle-outline' : 'document-text-outline'} size={18} color="#FFFFFF" />
              <Text style={styles.tosReadButtonText}>
                {termsAgreed ? 'Terms Read & Acknowledged ✓' : 'Read Terms of Service'}
              </Text>
            </TouchableOpacity>
          </View>
        </View>

        <View style={styles.buttonContainer}>
          <TouchableOpacity style={styles.backButton} onPress={onBackPress}>
            <Text style={styles.backButtonText}>Back</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[
              styles.nextButton,
              !isFormValid() && styles.nextButtonDisabled
            ]}
            onPress={handleComplete}
            disabled={!isFormValid()}
          >
            <Text style={[
              styles.nextButtonText,
              !isFormValid() && styles.nextButtonTextDisabled
            ]}>
              Next
            </Text>
          </TouchableOpacity>
        </View>

      </KeyboardAwareScrollView>

      {/* Terms of Service Modal */}
      <Modal visible={showTermsModal} animationType="slide" transparent onRequestClose={() => setShowTermsModal(false)}>
        <View style={styles.tosModalOverlay}>
          <View style={styles.tosModalContainer}>
            <View style={styles.tosModalHeader}>
              <Text style={styles.tosModalTitle}>Terms of Service</Text>
              <TouchableOpacity onPress={() => setShowTermsModal(false)} style={styles.tosModalClose}>
                <Ionicons name="close" size={22} color="#1E3A5F" />
              </TouchableOpacity>
            </View>
            <ScrollView
              style={styles.tosModalScroll}
              contentContainerStyle={styles.tosScrollContent}
              showsVerticalScrollIndicator={true}
              scrollIndicatorInsets={{ right: 1 }}
              onScroll={({ nativeEvent }) => {
                const { contentOffset, contentSize, layoutMeasurement } = nativeEvent;
                if (contentOffset.y + layoutMeasurement.height >= contentSize.height - 30) {
                  setHasReadTerms(true);
                }
              }}
              scrollEventThrottle={16}
            >
              <Text style={styles.tosText}>{LEGATURA_TOS}</Text>
            </ScrollView>
            {!hasReadTerms ? (
              <View style={styles.tosScrollHint}>
                <Ionicons name="arrow-down" size={14} color="#92400E" />
                <Text style={styles.tosScrollHintText}>  Scroll down to read the full Terms of Service</Text>
              </View>
            ) : null}
            <TouchableOpacity
              style={[styles.tosAcknowledgeButton, !hasReadTerms && styles.tosAcknowledgeButtonDisabled]}
              disabled={!hasReadTerms}
              onPress={() => { setTermsAgreed(true); setShowTermsModal(false); }}
            >
              <Text style={styles.tosAcknowledgeButtonText}>
                {hasReadTerms ? 'I Acknowledge & Agree' : 'Read to Continue'}
              </Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>

      {/* ID Type Selector Modal */}
      <Modal
        visible={showIdTypeModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowIdTypeModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select ID Type</Text>
              <TouchableOpacity
                onPress={() => setShowIdTypeModal(false)}
                style={styles.closeButton}
              >
                <MaterialIcons name="close" size={24} color="#333333" />
              </TouchableOpacity>
            </View>

            <TextInput
              style={styles.searchInput}
              value={idTypeSearch}
              onChangeText={handleIdTypeSearch}
              placeholder="Search ID types..."
              placeholderTextColor="#999"
              autoFocus
            />

            {filteredIdTypes.length === 0 ? (
              <View style={styles.emptyContainer}>
                <Text style={styles.emptyText}>No valid IDs available. Please check your connection.</Text>
              </View>
            ) : (
            <FlatList
              data={filteredIdTypes}
                keyExtractor={(item) => item.id.toString()}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.idTypeItem}
                  onPress={() => selectIdType(item)}
                >
                    <Text style={styles.idTypeText}>{item.valid_id_name || item.name}</Text>
                </TouchableOpacity>
              )}
              style={styles.idTypeList}
              showsVerticalScrollIndicator={false}
            />
            )}
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FEFEFE',
  },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: 30,
    paddingBottom: 40,
  },
  logoContainer: {
    alignItems: 'center',
    marginTop: 40,
    marginBottom: 30,
  },
  logo: {
    width: 200,
    height: 120,
  },
  progressContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 40,
    paddingHorizontal: 10,
  },
  progressStep: {
    flex: 1,
    alignItems: 'center',
  },
  progressBar: {
    height: 4,
    backgroundColor: '#E5E5E5',
    borderRadius: 2,
    width: '100%',
    marginBottom: 8,
  },
  progressBarActive: {
    backgroundColor: '#EC7E00',
  },
  progressText: {
    fontSize: 12,
    color: '#999999',
    textAlign: 'center',
  },
  progressTextActive: {
    color: '#333333',
    fontWeight: '600',
  },
  formContainer: {
    flex: 1,
    gap: 20,
  },
  inputContainer: {
    marginBottom: 4,
  },
  input: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E5E5E5',
    borderRadius: 12,
    paddingHorizontal: 16,
    paddingVertical: 16,
    fontSize: 16,
    color: '#333333',
  },
  dropdownContainer: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E5E5E5',
    borderRadius: 12,
  },
  dropdownInputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 16,
    justifyContent: 'space-between',
  },
  dropdownInputText: {
    fontSize: 16,
    color: '#333333',
    flex: 1,
  },
  dropdownInput: {
    paddingRight: 50,
  },
  dropdownIcon: {
    marginLeft: 10,
  },
  placeholderText: {
    color: '#999999',
  },
  uploadSection: {
    marginBottom: 4,
  },
  uploadLabel: {
    fontSize: 16,
    color: '#666666',
    marginBottom: 8,
  },
  uploadContainer: {
    backgroundColor: '#FFFFFF',
    borderWidth: 2,
    borderColor: '#EC7E00',
    borderStyle: 'dashed',
    borderRadius: 12,
    paddingVertical: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  uploadText: {
    fontSize: 16,
    color: '#CCCCCC',
    marginTop: 8,
  },
  buttonContainer: {
    flexDirection: 'row',
    gap: 15,
    marginTop: 40,
  },
  backButton: {
    flex: 1,
    backgroundColor: '#F5F5F5',
    borderRadius: 12,
    paddingVertical: 18,
    alignItems: 'center',
  },
  backButtonText: {
    color: '#666666',
    fontSize: 18,
    fontWeight: '600',
  },
  nextButton: {
    flex: 1,
    backgroundColor: '#EC7E00',
    borderRadius: 12,
    paddingVertical: 18,
    alignItems: 'center',
    shadowColor: '#EC7E00',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 8,
  },
  nextButtonText: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: '600',
  },
  nextButtonDisabled: {
    backgroundColor: '#CCCCCC',
    shadowColor: '#CCCCCC',
  },
  nextButtonTextDisabled: {
    color: '#999999',
  },
  tosCard: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E2E8F0',
    borderRadius: 4,
    padding: 14,
    marginTop: 20,
    marginBottom: 4,
    elevation: 1,
  },
  tosCardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  tosCardTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#1E3A5F',
    marginLeft: 8,
    letterSpacing: 0.3,
  },
  tosDivider: {
    height: 1,
    backgroundColor: '#E2E8F0',
    marginBottom: 10,
  },
  tosCardBody: {
    fontSize: 13,
    color: '#64748B',
    marginBottom: 12,
    lineHeight: 19,
  },
  tosReadButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#1E3A5F',
    borderRadius: 8,
    paddingVertical: 10,
    paddingHorizontal: 12,
  },
  tosReadButtonAgreed: {
    backgroundColor: '#10B981',
  },
  tosReadButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#FFFFFF',
    marginLeft: 8,
  },
  tosModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)',
    justifyContent: 'flex-end',
  },
  tosModalContainer: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 16,
    borderTopRightRadius: 16,
    height: '85%',
  },
  tosModalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },
  tosModalTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1E3A5F',
  },
  tosModalClose: {
    padding: 4,
  },
  tosModalScroll: {
    flex: 1,
  },
  tosScrollContent: {
    padding: 16,
    paddingBottom: 32,
  },
  tosText: {
    fontSize: 13,
    color: '#334155',
    lineHeight: 22,
  },
  tosScrollHint: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 8,
    backgroundColor: '#FEF3C7',
  },
  tosScrollHintText: {
    fontSize: 12,
    color: '#92400E',
    fontWeight: '500',
  },
  tosAcknowledgeButton: {
    marginHorizontal: 16,
    marginTop: 12,
    marginBottom: 20,
    backgroundColor: '#EC7E00',
    borderRadius: 8,
    paddingVertical: 14,
    alignItems: 'center',
  },
  tosAcknowledgeButtonDisabled: {
    backgroundColor: '#CBD5E1',
  },
  tosAcknowledgeButtonText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  validationTextContainer: {
    marginTop: 15,
    paddingHorizontal: 5,
  },
  validationText: {
    fontSize: 13,
    color: '#666666',
    textAlign: 'center',
    lineHeight: 18,
    fontStyle: 'italic',
  },
  sectionSeparator: {
    marginVertical: 20,
    paddingVertical: 15,
    borderTopWidth: 1,
    borderTopColor: '#E5E5E5',
    alignItems: 'center',
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333333',
    textAlign: 'center',
  },
  uploadedImage: {
    width: '100%',
    height: 150,
    borderRadius: 8,
    resizeMode: 'cover',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContainer: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    width: '90%',
    maxHeight: '80%',
    paddingVertical: 20,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    marginBottom: 15,
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333333',
  },
  closeButton: {
    padding: 4,
  },
  searchInput: {
    backgroundColor: '#F5F5F5',
    borderRadius: 12,
    paddingHorizontal: 16,
    paddingVertical: 12,
    fontSize: 16,
    marginHorizontal: 20,
    marginBottom: 15,
    color: '#333333',
  },
  idTypeList: {
    maxHeight: 400,
  },
  idTypeItem: {
    paddingVertical: 15,
    paddingHorizontal: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  idTypeText: {
    fontSize: 16,
    color: '#333333',
  },
  emptyContainer: {
    padding: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: '#999999',
    textAlign: 'center',
  },
  errorContainer: {
    backgroundColor: '#FFF3CD',
    borderWidth: 1,
    borderColor: '#FFC107',
    borderRadius: 12,
    padding: 16,
  },
  errorText: {
    fontSize: 14,
    color: '#856404',
    textAlign: 'center',
  },
});
