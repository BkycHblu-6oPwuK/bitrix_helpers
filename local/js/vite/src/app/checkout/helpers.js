export const formattedDate = (date) => {
    if (date instanceof Date) {
        return date.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
    }
    return date;
};