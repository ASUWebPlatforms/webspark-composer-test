import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faInfoCircle, faMinusCircle, faPlusCircle } from "@fortawesome/free-solid-svg-icons";

class Filters extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      status: "loading...",
      categories: [],
      tools: [],
      types: [],
      typesFilter: "",
      toolsFilter: "collapse",
      categoriesFilter: "collapse",
    };
  }

  toggleFilter(filter) {
    console.log(filter);

    switch (filter) {
      case "types":
        this.setState({
          typesFilter: this.state.typesFilter === "collapse" ? "" : "collapse",
        });
        break;
      case "tools":
        this.setState({
          toolsFilter: this.state.toolsFilter === "collapse" ? "" : "collapse",
        });
        break;
      case "categories":
        this.setState({
          categoriesFilter:
            this.state.categoriesFilter === "collapse" ? "" : "collapse",
        });
        break;

      default:
        break;
    }
  }

  getFilters() {
    fetch("/lx-resource-filters")
      .then((data) => {
        return data.json();
      })
      .then((terms) => {
        console.log(terms);
        let categories = [];
        let tools = [];
        let types = [];

        for (const term of terms) {
          switch (term.vid) {
            case "LX Resource Categories":
              categories.push({
                name: term.name,
                term_id: term.tid,
                term_description: term.description
              });
              break;

            case "LX Resource Tools":
              tools.push({
                name: term.name,
                term_id: term.tid,
                term_description: term.description
              });
              break;

            case "LX Resource Type":
              types.push({
                name: term.name,
                term_id: term.tid,
                term_description: term.description
              });
              break;

            default:
              //create other category
              console.log(
                `${term.name}|${term.tid} not in a default fliter category`
              );
              break;
          }
        }

        this.setState({
          status: "Done",
          categories: categories,
          tools: tools,
          types: types,
        });
      })
      .catch((error) => {
        console.log(error);
        this.setState({
          status: "Error",
        });
      });
  }

  componentDidMount() {
    this.getFilters();
  }

  componentWillUnmount() { }

  render() {
    let categoriesList = this.state.categories.map((category, i) => {
      return (
        <li>
          <label for={"box-" + category.term_id}>
            <input
              type="checkbox"
              onChange={() =>
                this.props.onFilterChange(category.term_id, "Category")
              }
              id={"box-" + category.term_id}
              name={"box-" + category.term_id}
              value={category.term_id}
            />
            &nbsp;{category.name}
          </label>
          {category.term_description.length > 0 ? ( <div class="filter-icon-tooltip">
            <span class="filter-icon-tooltiptext">{category.term_description}</span>
            <FontAwesomeIcon icon={faInfoCircle} />
          </div>) : ("")}
        </li>
      );
    });

    let toolsList = this.state.tools.map((tool) => {
      return (
        <li>
          <label for={"box-" + tool.term_id}>
            <input
              type="checkbox"
              onChange={() => this.props.onFilterChange(tool.term_id, "Tools")}
              id={"box-" + tool.term_id}
              name={"box-" + tool.term_id}
              value={tool.term_id}
            />
            &nbsp;{tool.name}
          </label>
          {tool.term_description.length > 0 ? ( <div class="filter-icon-tooltip">
            <span class="filter-icon-tooltiptext">{tool.term_description}</span>
            <FontAwesomeIcon icon={faInfoCircle} />
          </div>) : ("")}
        </li>
      );
    });

    let typesList = this.state.types.map((type) => {
      return (
        <li>
          <label for={"box-" + type.term_id}>
            <input
              type="checkbox"
              onChange={() => this.props.onFilterChange(type.term_id, "Type")}
              id={"box-" + type.term_id}
              name={"box-" + type.term_id}
              value={type.term_id}
            />
            &nbsp;{type.name}
          </label>
          {type.term_description.length > 0 ? ( <div class="filter-icon-tooltip">
            <span class="filter-icon-tooltiptext">{type.term_description}</span>
            <FontAwesomeIcon icon={faInfoCircle} />
          </div>) : ("")}
        </li>
      );
    });

    return (
      <div className="filter-heading">
        <h5 onClick={() => this.toggleFilter("types")}>
          Type{" "}
          {this.state.typesFilter ? (
            <FontAwesomeIcon icon={faPlusCircle} />
          ) : (
            <FontAwesomeIcon icon={faMinusCircle} />
          )}
        </h5>
        <div className={this.state.typesFilter}>
          <ul>
            {typesList}
          </ul>
        </div>
        <h5 onClick={() => this.toggleFilter("tools")}>
          Tools{" "}
          {this.state.toolsFilter ? (
            <FontAwesomeIcon icon={faPlusCircle} />
          ) : (
            <FontAwesomeIcon icon={faMinusCircle} />
          )}
        </h5>
        <div className={this.state.toolsFilter}>
          <ul>
            {toolsList}
          </ul>
        </div>
        <h5 onClick={() => this.toggleFilter("categories")}>
          Category{" "}
          {this.state.categoriesFilter ? (
            <FontAwesomeIcon icon={faPlusCircle} />
          ) : (
            <FontAwesomeIcon icon={faMinusCircle} />
          )}
        </h5>
        <div className={this.state.categoriesFilter}>
          <ul>
            {categoriesList}
          </ul>
        </div>
      </div>
    );
  }
}

export default Filters;