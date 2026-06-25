import { createRoot } from 'react-dom/client';
import App from './LoanApp';

const element = document.getElementById('loan-proration-block');
if (element) {
  createRoot(element).render(<App />);
} else {
  console.log(
    "Hello from loan proration tool! Please add the block with id 'loan-proration-block' to your page to see the React app.",
  );
}
