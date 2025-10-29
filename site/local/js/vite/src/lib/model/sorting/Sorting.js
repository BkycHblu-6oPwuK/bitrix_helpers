import SortingItem from "./SortingItem";

class Sorting {
    constructor(sorting) {
        this.currentSortId = sorting.currentSortId;
        this.defaultSortId = sorting.defaultSortId;
        this.title = sorting.title;
        this.availableSorting = sorting.availableSorting?.map(item => {
            return new SortingItem(item)
        });
    }
}

export default Sorting;