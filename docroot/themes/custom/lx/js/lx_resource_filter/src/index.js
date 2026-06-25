import Filters from "./components/Filters";
import InfiniteScrollComponent from "./components/InfiniteScrollComponent";

class App extends React.Component {
  constructor(props) {
    super(props);
    this.getResources = this.getResources.bind(this);
    this.onFilterChange = this.onFilterChange.bind(this);
    this.handleChange = this.handleChange.bind(this);
    this.filterSearchResults = this.filterSearchResults.bind(this);
    this.getFilters = this.getFilters.bind(this);
    this.state = {
      status: "loading...",
      filterQuery: [],
      resources: [],
      searchQuery: "",
      filterSearchUrl: "",
      queryFilter: {},
      allFilters: [],
    };
  }

  getResources(offset) {
    console.log("get resources called", offset);
    let searchQuery = "";
    console.log("filter or search present");
    if (this.state.searchQuery.length != 0) {
      searchQuery =
        "filter[lx_resource-title][path]=title&filter[lx_resource-title][operator]=CONTAINS&filter[lx_resource-title][value]=" +
        this.state.searchQuery;
    }
    fetch(
      "/jsonapi/node/lx_resource?" +
        searchQuery +
        this.state.filterSearchUrl +
        "&page[limit]=20&page[offset]=" +
        offset
    )
      .then((data) => {
        return data.json();
      })
      .then((resp) => {
        console.log("get resources response: ", resp);
        this.setState({
          status: "Done",
          resources: this.state.resources.concat(resp.data),
        });
      })
      .catch((error) => {
        console.log(error);
        this.setState({
          status: "Error",
        });
      });
  }

  getFilters(filter) {
    fetch("/lx-resource-filters")
      .then((data) => {
        return data.json();
      })
      .then((terms) => {
        this.setState(
          {
            allFilters: terms,
          },
          () => {
            let filterObj = [];
            if (filter !== "") {
              filterObj = this.state.allFilters.filter((filterName) => {
                return filterName.name == filter;
              });
              this.setState({ queryFilter: filterObj[0] }, () => {
                let filterType = "";
                if (this.state.queryFilter.vid.includes("Tools")) {
                  filterType = "Tools";
                } else if (this.state.queryFilter.vid.includes("Type")) {
                  filterType = "Type";
                } else if (this.state.queryFilter.vid.includes("Category")) {
                  filterType = "Category";
                }
                this.onFilterChange(this.state.queryFilter.tid, filterType);
              });
            }
          }
        );
      });
  }

  filterSearchResults(query, filterSearchUrl) {
    let searchQuery = "";
    if (query.length != 0) {
      searchQuery =
        "filter[lx_resource-title][path]=title&filter[lx_resource-title][operator]=CONTAINS&filter[lx_resource-title][value]=" +
        query;
    }
    fetch("/jsonapi/node/lx_resource?" + searchQuery + filterSearchUrl)
      .then((data) => {
        return data.json();
      })
      .then((resp) => {
        this.setState({
          status: "Done",
          resources: resp.data,
        });
      })
      .catch((error) => {
        this.setState({
          status: "Error",
        });
      });
  }

  onFilterChange(term_id, filter_type) {
    let filterSearchUrl = "";

    let queryArray = this.state.filterQuery;
    const filterIndex = queryArray.findIndex((object) => {
      return object.tid === term_id;
    });
    if (filterIndex == -1) {
      queryArray.push({ tid: term_id, filter_type: filter_type });
    } else {
      queryArray.splice(filterIndex, 1);
    }
    this.setState({
      filterQuery: queryArray,
    });
    console.log("Filter Query: ");
    this.state.filterQuery.forEach((query) => {
      for (let key in query) {
        console.log(`${key}: ${query[key]}`);
      }
    });
    let tid_t = [];
    let tid_c = [];
    let tid_tls = [];

    let tid_t_obj = this.state.filterQuery.filter(
      (query) => query["filter_type"] == "Type"
    );
    let tid_tls_obj = this.state.filterQuery.filter(
      (query) => query["filter_type"] == "Tools"
    );
    let tid_c_obj = this.state.filterQuery.filter(
      (query) => query["filter_type"] == "Category"
    );

    if (tid_c_obj.length > 0) {
      tid_c_obj.forEach((obj) => {
        tid_c.push(obj["tid"]);
      });
      console.log("c", tid_c);
    }
    if (tid_t_obj.length > 0) {
      tid_t_obj.forEach((obj) => {
        tid_t.push(obj["tid"]);
      });
      console.log("t", tid_t);
    }
    if (tid_tls_obj.length > 0) {
      tid_tls_obj.forEach((obj) => {
        tid_tls.push(obj["tid"]);
      });
      console.log("tls", tid_tls);
    }
    if (tid_tls != 0) {
      tid_tls.forEach((tls) => {
        filterSearchUrl +=
          "&filter[lx_resource-field_resource_tools][path]=field_resource_tools.meta.drupal_internal__target_id&filter[lx_resource-field_resource_tools][value]=" +
          tls;
      });
    }
    if (tid_c != 0) {
      tid_c.forEach((c) => {
        filterSearchUrl +=
          "&filter[lx_resource-field_resource_categories][path]=field_resource_categories.meta.drupal_internal__target_id&filter[lx_resource-field_resource_categories][value]=" +
          c;
      });
    }
    if (tid_t != 0) {
      tid_t.forEach((t) => {
        filterSearchUrl +=
          "&filter[lx_resource-field_resource_type][path]=field_resource_type.meta.drupal_internal__target_id&filter[lx_resource-field_resource_type][value]=" +
          t;
      });
    }

    console.log("FilterSearchUrl: " + filterSearchUrl);

    this.setState({ filterSearchUrl: filterSearchUrl }, () => {
      this.filterSearchResults(
        this.state.searchQuery,
        this.state.filterSearchUrl
      );
    });
  }

  handleChange = (e) => {
    this.setState({ searchQuery: e.target.value }, () => {
      this.filterSearchResults(
        this.state.searchQuery,
        this.state.filterSearchUrl
      );
    });
  };

  componentDidMount() {
    console.log("app mount");
    this.getResources(0);
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get("filter_type");
    if (filter != undefined || filter != null) {
      this.getFilters(filter);
    }
  }

  componentWillUnmount() {}

  render() {
    return (
      <div className="lx-resources-app">
        <div class="row">
          <div class="col-6 col-md-2 lx-resource-app-filters sticky-filter">
            <h3>Filters</h3>
            <Filters onFilterChange={this.onFilterChange} />
          </div>
          <div class="col-12 col-sm-6 col-md-10 lx-resource-app-cards">
            <input
              className="form-control"
              type="search"
              placeholder="Search Resources"
              aria-label="Search Resources"
              onChange={this.handleChange}
            />
            {(this.state.searchQuery || this.state.filterQuery.length > 0) &&
            this.state.resources.length == 0 ? (
              <div>
                {" "}
                <h3>No Resources Found</h3>{" "}
              </div>
            ) : (
              <InfiniteScrollComponent
                getResources={this.getResources}
                filterQuery={this.state.filterQuery}
                status={this.state.status}
                resources={this.state.resources}
              />
            )}
          </div>
        </div>
      </div>
    );
  }
}

ReactDOM.render(<App />, document.getElementById("lx-resource-custom-app"));
