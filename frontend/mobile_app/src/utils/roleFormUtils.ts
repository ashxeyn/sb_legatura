// Shared helpers for role application forms (web + mobile)
export const computeYears = (start?: string): number => {
  if (!start) return 0;
  const sel = new Date(start);
  const now = new Date();
  let years = now.getFullYear() - sel.getFullYear();
  if (now.getMonth() < sel.getMonth() || (now.getMonth() === sel.getMonth() && now.getDate() < sel.getDate())) {
    years -= 1;
  }
  return Math.max(0, years);
};

export const formatExperience = (start?: string): string => {
  if (!start) return '';
  const sel = new Date(start);
  const now = new Date();
  let years = now.getFullYear() - sel.getFullYear();
  let months = (now.getMonth() - sel.getMonth()) + (years * 12);
  if (now.getDate() < sel.getDate()) months -= 1;
  if (months < 0) months = 0;
  years = Math.floor(months / 12);
  const remMonths = months % 12;
  if (years >= 1) {
    return `${years} ${years === 1 ? 'year' : 'years'}${remMonths ? ` ${remMonths} ${remMonths === 1 ? 'month' : 'months'}` : ''}`;
  }
  return `${remMonths} ${remMonths === 1 ? 'month' : 'months'}`;
};

export const formatDate = (date: Date): string => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

export const formatDateForDisplay = (dateString?: string): string => {
  if (!dateString) return '';
  try {
    const date = new Date(dateString);
    const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
  } catch {
    return dateString || '';
  }
};

export default {};
